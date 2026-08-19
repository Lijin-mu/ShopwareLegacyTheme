<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Controller;

use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use ShopwareLegacyTheme\Core\Event\EnquiryFormSubmittedEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class EnquiryFormController extends StorefrontController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route(
        path: '/enquiry-form/submit',
        name: 'frontend.enquiry.form.submit',
        methods: ['POST'],
        defaults: ['XmlHttpRequest' => true]
    )]
    /**
     * Handle AJAX enquiry form submissions.
     *
     * @param Request $request
     * @param RequestDataBag $data
     * @param SalesChannelContext $context
     *
     * @return JsonResponse
     */
    public function submit(Request $request, RequestDataBag $data, SalesChannelContext $context): JsonResponse
    {
        try {
            $rawData = json_decode($request->getContent(), true);
            if (!is_array($rawData)) {
                return $this->createErrorResponse('legacytheme.enquiryForm.invalidPayload', 400);
            }

            $formData = $this->extractFormData($rawData);

            if ($validationError = $this->validateFormData($formData)) {
                return $validationError;
            }

            return $this->dispatchEnquiryEvent($formData, $context);
        } catch (\Throwable $e) {
            $this->logger->error('Enquiry form submission failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('legacytheme.enquiryForm.generalErrorMessage');
        }
    }

    private function extractFormData(array $rawData): array
    {
        return [
            'salutationDisplay' => $rawData['salutation'] ?? '',
            'firstName' => $rawData['firstName'] ?? '',
            'lastName' => $rawData['lastName'] ?? '',
            'email' => $rawData['email'] ?? '',
            'phone' => $rawData['phone'] ?? '',
            'description' => $rawData['description'] ?? '',
            'productName' => $rawData['productName'] ?? '',
        ];
    }

    private function validateFormData(array $formData): ?JsonResponse
    {
        $violations = $this->validator->validate($formData, $this->getValidationConstraints());

        if ($violations->count() === 0) {
            return null;
        }

        $errors = [];
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return $this->createValidationErrorResponse($errors);
    }

    private function dispatchEnquiryEvent(array $formData, SalesChannelContext $context): JsonResponse
    {
        $event = new EnquiryFormSubmittedEvent(
            $context->getSalesChannel()->getId(),
            $formData,
            $context->getContext()
        );

        try {
            $this->logger->info('Dispatching enquiry form event', [
                'event_name' => EnquiryFormSubmittedEvent::EVENT_NAME,
                'email' => $formData['email'],
                'firstName' => $formData['firstName'],
                'lastName' => $formData['lastName'],
                'salesChannelId' => $context->getSalesChannel()->getId(),
            ]);

            $this->eventDispatcher->dispatch($event, EnquiryFormSubmittedEvent::EVENT_NAME);

            $this->logger->info('Enquiry form event dispatched successfully', [
                'email' => $formData['email'],
                'firstName' => $formData['firstName'],
                'lastName' => $formData['lastName'],
            ]);

            return $this->createSuccessResponse();
        } catch (\Throwable $eventException) {
            $this->logger->error('Failed to dispatch enquiry form event', [
                'error' => $eventException->getMessage(),
                'trace' => $eventException->getTraceAsString(),
                'email' => $formData['email'],
            ]);

            return $this->createErrorResponse('legacytheme.enquiryForm.partialErrorMessage', 500);
        }
    }

    private function getValidationConstraints(): Assert\Collection
    {
        return new Assert\Collection([
            'salutationDisplay' => new Assert\Optional([
                new Assert\Length(['min' => 2, 'max' => 100]),
            ]),
            'firstName' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 100])],
            'lastName' => [new Assert\NotBlank(), new Assert\Length(['min' => 2, 'max' => 100])],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'phone' => new Assert\Optional([
                new Assert\Length(['min' => 5, 'max' => 20]),
            ]),
            'description' => new Assert\Optional([
                new Assert\Length(['max' => 1000]),
            ]),
            'productName' => new Assert\Optional([
                new Assert\Length(['max' => 255]),
            ]),
        ]);
    }

    private function createValidationErrorResponse(array $errors): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'errors' => $errors,
        ], 400);
    }

    private function createSuccessResponse(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $this->trans('legacytheme.enquiryForm.successMessage'),
        ]);
    }

    private function createErrorResponse(string $translationKey, int $statusCode = 500): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $this->trans($translationKey),
        ], $statusCode);
    }
}

