<?php

namespace SyliusUnzerPlugin\Services;

use Unzer\Core\Infrastructure\Configuration\Configuration;

/**
 * Class ConfigurationService.
 *
 * @package SyliusUnzerPlugin\Services
 */
class ConfigurationService extends Configuration
{

    /**
     * @inheritDoc
     */
    public function getIntegrationName(): string
    {
        return 'SyliusUnzer';
    }

    /**
     * @inheritDoc
     */
    public function getAsyncProcessUrl(string $guid): string
    {
        return 'https://sylius.unzer.com/integration/async-process/';
    }
}
