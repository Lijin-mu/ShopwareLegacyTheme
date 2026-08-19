<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Cms\DataResolver\Element;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use ShopwareLegacyTheme\Core\Content\Cms\SalesChannel\Struct\ProductReturnFormStruct;
use Symfony\Component\HttpFoundation\RequestStack;

class ProductReturnFormElementResolver extends AbstractCmsElementResolver
{
    private const DEFAULT_BUTTON_TEXT = 'Submit Return Request';
    private const DEFAULT_SUCCESS_MESSAGE = 'Your return request has been submitted successfully!';

    public function __construct(
        private readonly EntityRepository $salutationRepository,
        private readonly EntityRepository $productRepository,
        private readonly RequestStack $requestStack
    ) {
    }

    public function getType(): string
    {
        return 'product-return-form';
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $data = $this->createStructFromConfig($slot);
        $salesChannelContext = $resolverContext->getSalesChannelContext();

        $this->addSalutationsIfNeeded($data, $salesChannelContext);
        $this->prefillProductInformation($data, $salesChannelContext);

        $slot->setData($data);
    }

    private function createStructFromConfig(CmsSlotEntity $slot): ProductReturnFormStruct
    {
        $config = $slot->getFieldConfig();
        $data = new ProductReturnFormStruct();

        $data->setTitle((string) $this->getConfigValue($config, 'title', ''));
        $data->setSubtitle((string) $this->getConfigValue($config, 'subtitle', ''));
        $data->setShowLabel((bool) $this->getConfigValue($config, 'showLabel', true));
        $data->setShowSalutation((bool) $this->getConfigValue($config, 'showSalutation', false));
        $data->setShowPhone((bool) $this->getConfigValue($config, 'showPhone', true));
        $data->setShowDescription((bool) $this->getConfigValue($config, 'showDescription', true));
        $data->setShowProductNumber((bool) $this->getConfigValue($config, 'showProductNumber', true));
        $data->setShowReason((bool) $this->getConfigValue($config, 'showReason', true));
        $data->setButtonText(
            (string) $this->getConfigValue($config, 'buttonText', self::DEFAULT_BUTTON_TEXT)
        );
        $data->setSuccessMessage(
            (string) $this->getConfigValue($config, 'successMessage', self::DEFAULT_SUCCESS_MESSAGE)
        );

        $reasonOptionsValue = (string) $this->getConfigValue($config, 'reasonOptions', '');
        $data->setReasonOptions($this->parseReasonOptions($reasonOptionsValue));

        return $data;
    }

    /**
     * @return string[]
     */
    private function parseReasonOptions(string $raw): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $raw) ?: [];

        return array_values(array_filter(array_map(static fn(string $item): string => trim($item), $lines)));
    }

    private function getConfigValue($config, string $key, mixed $default)
    {
        $field = $config->get($key);

        if ($field === null) {
            return $default;
        }

        $value = $field->getValue();

        return $value ?? $default;
    }

    private function addSalutationsIfNeeded(
        ProductReturnFormStruct $data,
        SalesChannelContext $salesChannelContext
    ): void {
        if (!$data->getShowSalutation()) {
            return;
        }

        $data->setSalutations($this->loadSalutations($salesChannelContext));
    }

    private function loadSalutations(SalesChannelContext $salesChannelContext): ?EntitySearchResult
    {
        $criteria = (new Criteria())
            ->addSorting(new FieldSorting('salutationKey', FieldSorting::ASCENDING));

        return $this->salutationRepository->search(
            $criteria,
            $salesChannelContext->getContext()
        );
    }

    private function prefillProductInformation(
        ProductReturnFormStruct $data,
        SalesChannelContext $salesChannelContext
    ): void {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $productNumber = (string) ($request->query->get('productNumber') ?? $request->query->get('sOrdernumber') ?? '');
        $productNameFromQuery = (string) ($request->query->get('productName') ?? '');

        if ($productNameFromQuery !== '') {
            $data->setProductName($productNameFromQuery);
        }

        if ($productNumber === '') {
            return;
        }

        $product = $this->loadProductByProductNumber($productNumber, $salesChannelContext->getContext());
        if ($product) {
            $data->setProductName($product->getTranslation('name') ?? $product->getName());
            $data->setProductNumber($product->getProductNumber());
        } else {
            $data->setProductNumber($productNumber);
        }
    }

    private function loadProductByProductNumber(string $productNumber, $context): ?ProductEntity
    {
        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('productNumber', $productNumber))
            ->addAssociation('translations')
            ->setLimit(1);

        $searchResult = $this->productRepository->search($criteria, $context);

        if ($searchResult->count() === 0) {
            return null;
        }

        $elements = $searchResult->getElements();

        return reset($elements) ?: null;
    }
}

