<?php declare(strict_types=1);

namespace ShopwareLegacyTheme\Core\Framework;

use Shopware\Core\Framework\Event\BusinessEventRegistry;
use ShopwareLegacyTheme\Core\Event\EnquiryFormSubmittedEvent;
use ShopwareLegacyTheme\Core\Event\ProductReturnFormSubmittedEvent;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BusinessEventCollectorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(BusinessEventRegistry::class)) {
            return;
        }

        $definition = $container->getDefinition(BusinessEventRegistry::class);
        $definition->addMethodCall('addClasses', [
            [
                EnquiryFormSubmittedEvent::class,
                ProductReturnFormSubmittedEvent::class,
            ]
        ]);
    }
}

