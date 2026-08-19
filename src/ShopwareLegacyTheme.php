<?php declare(strict_types=1);

namespace ShopwareLegacyTheme;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Storefront\Framework\ThemeInterface;
use ShopwareLegacyTheme\Core\Content\Flow\EnquiryFormFlowInstaller;
use ShopwareLegacyTheme\Core\Content\Flow\ProductReturnFormFlowInstaller;
use ShopwareLegacyTheme\Core\Framework\BusinessEventCollectorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ShopwareLegacyTheme extends Plugin implements ThemeInterface
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new BusinessEventCollectorPass());
    }

    public function install(InstallContext $installContext): void
    {
        parent::install($installContext);

        try {
            /** @var Connection $connection */
            $connection = $this->container->get(Connection::class);

            // Install enquiry form flow
            $enquiryFlowInstaller = new EnquiryFormFlowInstaller($connection);
            $enquiryFlowInstaller->install();

            // Install product return form flow
            $productReturnFlowInstaller = new ProductReturnFormFlowInstaller($connection);
            $productReturnFlowInstaller->install();
        } catch (\Exception $e) {
            if ($this->container->has('logger')) {
                $this->container->get('logger')->warning(
                    'Failed to install flow: ' . $e->getMessage(),
                    ['exception' => $e]
                );
            }
        }
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        parent::uninstall($uninstallContext);

        if ($uninstallContext->keepUserData()) {
            return;
        }

        try {
            /** @var Connection $connection */
            $connection = $this->container->get(Connection::class);

            // Uninstall enquiry form flow
            $enquiryFlowInstaller = new EnquiryFormFlowInstaller($connection);
            $enquiryFlowInstaller->uninstall();

            // Uninstall product return form flow
            $productReturnFlowInstaller = new ProductReturnFormFlowInstaller($connection);
            $productReturnFlowInstaller->uninstall();
        } catch (\Exception $e) {
            if ($this->container->has('logger')) {
                $this->container->get('logger')->warning(
                    'Failed to uninstall flow: ' . $e->getMessage(),
                    ['exception' => $e]
                );
            }
        }
    }
}