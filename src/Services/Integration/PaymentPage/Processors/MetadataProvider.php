<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors;

use Composer\InstalledVersions;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\MetadataProvider as MetadataProviderInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Metadata;

/**
 * Class MetadataProvider
 *
 * @package SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors
 */
class MetadataProvider implements MetadataProviderInterface
{

    public function get(PaymentPageCreateContext $context): Metadata
    {
        $shopVersion = InstalledVersions::getPrettyVersion('sylius/sylius');
        $pluginVersion = InstalledVersions::getPrettyVersion('unzer/sylius-plugin');
        return (new Metadata())
            ->setShopType('sylius')
            ->setShopVersion((string)$shopVersion)
            ->addMetadata('pluginType', 'unzerdev/sylius')
            ->addMetadata('pluginVersion', (string)$pluginVersion);
    }
}
