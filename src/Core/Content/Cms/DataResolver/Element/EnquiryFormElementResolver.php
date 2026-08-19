<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Content\Cms\DataResolver\Element;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Cms\DataResolver\Element\AbstractCmsElementResolver;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use ShopwareLegacyTheme\Core\Content\Cms\SalesChannel\Struct\EnquiryFormStruct;
use Symfony\Component\HttpFoundation\RequestStack;

class EnquiryFormElementResolver extends AbstractCmsElementResolver
{
    private EntityRepository $salutationRepository;
    private EntityRepository $productRepository;
    private RequestStack $requestStack;

    public function __construct(
        EntityRepository $salutationRepository,
        EntityRepository $productRepository,
        RequestStack $requestStack
    ) {
        $this->salutationRepository = $salutationRepository;
        $this->productRepository = $productRepository;
        $this->requestStack = $requestStack;
    }

    public function getType(): string
    {
        return 'enquiry-form';
    }

    /**
     * {@inheritdoc}
     */
    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $data = $this->createStructFromConfig($slot);

        $this->addSalutationsIfNeeded($data, $resolverContext);
        $this->prefillProductData($data, $resolverContext);

        $slot->setData($data);
    }

    private function createStructFromConfig(CmsSlotEntity $slot): EnquiryFormStruct
    {
        $config = $slot->getFieldConfig();
        $data = new EnquiryFormStruct();

        $data->setTitle($config->get('title')->getValue() ?? '');
        $data->setSubtitle($config->get('subtitle')->getValue() ?? '');
        $data->setShowLabel($config->get('showLabel')->getValue() ?? true);
        $data->setShowSalutation($config->get('showSalutation')->getValue() ?? true);
        $data->setShowPhone($config->get('showPhone')->getValue() ?? true);
        $data->setShowDescription($config->get('showDescription')->getValue() ?? true);
        $data->setButtonText($config->get('buttonText')->getValue() ?? 'Submit Enquiry');
        $data->setSuccessMessage($config->get('successMessage')->getValue() ?? 'Thank you for your enquiry!');

        return $data;
    }

    private function addSalutationsIfNeeded(EnquiryFormStruct $data, ResolverContext $resolverContext): void
    {
        if (!$data->getShowSalutation()) {
            return;
        }

        $criteria = new Criteria();
        $criteria->addSorting(new FieldSorting('salutationKey', FieldSorting::ASCENDING));

        $salutations = $this->salutationRepository->search(
            $criteria,
            $resolverContext->getSalesChannelContext()->getContext()
        );

        $data->setSalutations($salutations);
    }

    private function prefillProductData(EnquiryFormStruct $data, ResolverContext $resolverContext): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return;
        }

        $sInquiry = $request->query->get('sInquiry');
        $sOrdernumber = $request->query->get('sOrdernumber');

        if ($sInquiry !== 'detail' || !$sOrdernumber) {
            return;
        }

        $product = $this->loadProductByOrderNumber(
            $sOrdernumber,
            $resolverContext->getSalesChannelContext()->getContext()
        );

        if ($product === null) {
            return;
        }

        $translatedData = $product->getTranslated();
        $productName = $translatedData['name'] ?? $product->getName();

        $data->setProductName($productName);
        $data->setProductNumber($product->getProductNumber());
    }

    /**
     * Load a product by its order number (product number).
     *
     * @param string $orderNumber The product order number to search for
     * @param Context $context The Shopware context
     * @return ProductEntity|null The product entity if found, null otherwise
     */
    private function loadProductByOrderNumber(string $orderNumber, Context $context): ?ProductEntity
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $orderNumber));
        $criteria->addAssociation('translations');
        $criteria->setLimit(1);

        $searchResult = $this->productRepository->search($criteria, $context);

        if ($searchResult->count() === 0) {
            return null;
        }

        $elements = $searchResult->getElements();
        return reset($elements) ?: null;
    }
}

