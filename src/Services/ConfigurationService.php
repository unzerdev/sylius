<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services;

use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\Singleton;

/**
 * Class ConfigurationService.
 *
 * @package SyliusUnzerPlugin\Services
 */
class ConfigurationService extends Configuration
{
    /**
     * Singleton instance of this class.
     *
     * @var ?Singleton
     */
    protected static ?Singleton $instance = null;

    /**
     * @return ConfigurationService
     */
    public static function create(): ConfigurationService
    {
       return static::getInstance();
    }

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
