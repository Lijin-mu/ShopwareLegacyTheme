<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Storefront\Controller;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\PlatformRequest;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\NavigationController;
use Shopware\Storefront\Framework\Routing\StorefrontRouteScope;
use Shopware\Storefront\Pagelet\Footer\FooterPageletLoaderInterface;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoadedHook;
use Shopware\Storefront\Pagelet\Header\HeaderPageletLoaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Shopware\Storefront\Controller\StorefrontController;
use Shopware\Storefront\Pagelet\Menu\Offcanvas\MenuOffcanvasPageletLoadedHook;
use Shopware\Storefront\Pagelet\Menu\Offcanvas\MenuOffcanvasPageletLoaderInterface;

/**
 * @internal
 * Do not use direct or indirect repository calls in a controller. Always use a store-api route to get or put data
 */
#[Route(defaults: [PlatformRequest::ATTRIBUTE_ROUTE_SCOPE => [StorefrontRouteScope::ID]])]
#[Package('discovery')]
class HeaderController extends StorefrontController
{
    /**
     * @internal
     * @param NavigationController|object $decoratedController The decorated NavigationController or another decorator
     */
    public function __construct(
        private readonly object $decoratedController,
        private readonly HeaderPageletLoaderInterface $headerLoader,
        private readonly FooterPageletLoaderInterface $footerLoader,
        private readonly MenuOffcanvasPageletLoaderInterface $offcanvasLoader
    ) {
    }

    #[Route(
        path: '/',
        name: 'frontend.home.page',
        options: ['seo' => true],
        defaults: ['_httpCache' => true],
        methods: ['GET'],
    )]
    public function home(Request $request, SalesChannelContext $context): Response
    {
        return $this->decoratedController->home($request, $context);
    }

    #[Route(
        path: '/navigation/{navigationId}',
        name: 'frontend.navigation.page',
        options: ['seo' => true],
        defaults: ['_httpCache' => true],
        methods: ['GET'],
    )]
    public function index(SalesChannelContext $context, Request $request): Response
    {
        return $this->decoratedController->index($context, $request);
    }

    #[Route(
        path: '/widgets/menu/offcanvas',
        name: 'frontend.menu.offcanvas',
        defaults: ['XmlHttpRequest' => true, '_httpCache' => true],
        methods: ['GET'],
    )]
    public function offcanvas(Request $request, SalesChannelContext $context): Response
    {
        $page = $this->offcanvasLoader->load($request, $context);

        $footer = $this->footerLoader->load($request, $context);

        // Add service menu from footer to header
        if ($footer && method_exists($footer, 'getServiceMenu')) {
            $page->addExtension('serviceMenu', $footer->getServiceMenu());
        }

        $this->hook(new MenuOffcanvasPageletLoadedHook($page, $context));

        $response = $this->renderStorefront(
            '@Storefront/storefront/layout/navigation/offcanvas/navigation-pagelet.html.twig',
            ['page' => $page]
        );

        $response->headers->set('x-robots-tag', 'noindex');

        return $response;
    }

    #[Route(
        path: '/_esi/global/header',
        name: 'frontend.header',
        defaults: ['XmlHttpRequest' => true, '_httpCache' => true, '_esi' => true],
        methods: ['GET'],
    )]
    public function header(Request $request, SalesChannelContext $context): Response
    {
        $header = $this->headerLoader->load($request, $context);
        $footer = $this->footerLoader->load($request, $context);

        // Add service menu from footer to header
        if ($footer && method_exists($footer, 'getServiceMenu')) {
            $header->addExtension('serviceMenu', $footer->getServiceMenu());
        }

        $this->hook(new HeaderPageletLoadedHook($header, $context));

        return $this->renderStorefront('@Storefront/storefront/layout/header.html.twig', [
            'header' => $header,
            'headerParameters' => $request->get('headerParameters') ?? [],
        ]);
    }

    #[Route(
        path: '/_esi/global/footer',
        name: 'frontend.footer',
        defaults: ['XmlHttpRequest' => true, '_httpCache' => true, '_esi' => true],
        methods: ['GET'],
    )]
    public function footer(Request $request, SalesChannelContext $context): Response
    {
        return $this->decoratedController->footer($request, $context);
    }

}
