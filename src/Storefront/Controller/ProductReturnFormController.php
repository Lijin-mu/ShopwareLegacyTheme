<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Controller;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use ShopwareLegacyTheme\Core\Event\ProductReturnFormSubmittedEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Route(defaults: ['_routeScope' => ['storefront']])]
class ProductReturnFormController extends StorefrontController
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger
    ) {
    }

    #[Route(
        path: '/product-return-form/submit',
        name: 'frontend.product.return.form.submit',
        methods: ['POST'],
        defaults: ['XmlHttpRequest' => true]
    )]
    /**
     * Handle submissions of the product return form.
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
            $formData = $this->extractFormData($request);

            if ($privacyError = $this->validatePrivacyCheck($formData)) {
                return $privacyError;
            }

            if ($validationError = $this->validateFormData($formData)) {
                return $validationError;
            }

            return $this->dispatchProductReturnEvent($formData, $context);
        } catch (\Throwable $e) {
            $this->logger->error('Product return form submission failed', [
                'error' => $e->getMessage(),
            ]);

            return $this->createErrorResponse('legacytheme.productReturnForm.generalErrorMessage');
        }
    }

    private function extractFormData(Request $request): array
    {
        $rawData = json_decode($request->getContent(), true);
        if (!is_array($rawData)) {
            $rawData = [];
        }

        return [
            'customerNumber' => $rawData['customerNumber'] ?? '',
            'email' => $rawData['email'] ?? '',
            'invoiceNumber' => $rawData['invoiceNumber'] ?? '',
            'itemNumbers' => $rawData['itemNumbers'] ?? '',
            'comment' => $rawData['comment'] ?? '',
            'privacyCheck' => $rawData['privacyCheck'] ?? '',
            'productName' => $rawData['productName'] ?? '',
        ];
    }

    private function validatePrivacyCheck(array $formData): ?JsonResponse
    {
        if (!empty($formData['privacyCheck']) && $formData['privacyCheck'] === 'on') {
            return null;
        }

        return $this->createValidationErrorResponse([
            'privacyCheck' => $this->trans('legacytheme.productReturnForm.validation.privacyCheck'),
        ]);
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

    private function dispatchProductReturnEvent(array $formData, SalesChannelContext $context): JsonResponse
    {
        $event = new ProductReturnFormSubmittedEvent(
            $context->getSalesChannel()->getId(),
            $formData,
            $context->getContext()
        );

        try {
            $this->logger->info('Dispatching product return form event', [
                'event_name' => ProductReturnFormSubmittedEvent::EVENT_NAME,
                'email' => $formData['email'],
                'customerNumber' => $formData['customerNumber'],
                'salesChannelId' => $context->getSalesChannel()->getId(),
            ]);

            $this->eventDispatcher->dispatch($event, ProductReturnFormSubmittedEvent::EVENT_NAME);

            $this->logger->info('Product return form event dispatched successfully', [
                'email' => $formData['email'],
                'customerNumber' => $formData['customerNumber'],
            ]);

            return $this->createSuccessResponse();
        } catch (\Throwable $eventException) {
            $this->logger->error('Failed to dispatch product return form event', [
                'error' => $eventException->getMessage(),
                'trace' => $eventException->getTraceAsString(),
                'email' => $formData['email'],
                'customerNumber' => $formData['customerNumber'],
            ]);

            return $this->createErrorResponse('legacytheme.productReturnForm.partialErrorMessage', 500);
        }
    }

    private function getValidationConstraints(): Assert\Collection
    {
        return new Assert\Collection([
            'customerNumber' => [new Assert\NotBlank(), new Assert\Length(['min' => 1, 'max' => 100])],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'invoiceNumber' => [new Assert\NotBlank(), new Assert\Length(['min' => 1, 'max' => 100])],
            'itemNumbers' => [new Assert\NotBlank(), new Assert\Length(['min' => 1, 'max' => 500])],
            'comment' => new Assert\Optional([
                new Assert\Length(['max' => 2000]),
            ]),
            'privacyCheck' => new Assert\Optional(),
            'productName' => new Assert\Optional([
                new Assert\Length(['max' => 255]),
            ]),
        ]);
    }

    private function createSuccessResponse(): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'message' => $this->trans('legacytheme.productReturnForm.successMessage'),
        ]);
    }

    private function createValidationErrorResponse(array $errors): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'errors' => $errors,
        ], 400);
    }

    private function createErrorResponse(string $translationKey, int $statusCode = 500): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'message' => $this->trans($translationKey),
        ], $statusCode);
    }
}
