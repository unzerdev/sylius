<?php

namespace SyliusUnzerPlugin\Bootstrap;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Unzer\Core\BusinessLogic\BootstrapComponent;
use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\ServiceRegister;

/**
 * Class Bootstrap.
 *
 * @package SyliusUnzerPlugin\Bootstrap
 */
class Bootstrap extends BootstrapComponent
{
    /**
     * @var ShopLoggerAdapter
     */
    private static ShopLoggerAdapter $loggerAdapter;

    /**
     * @var ?ContainerInterface
     */
    private static ?ContainerInterface $container = null;
    /**
     * @var bool
     */
    private static bool $isInitialized = false;


    public function __construct(ShopLoggerAdapter $loggerAdapter)
    {
        self::$loggerAdapter = $loggerAdapter;
    }

    public static function boot(?ContainerInterface $container): void
    {
        self::$container = $container;

        if (!self::$isInitialized) {
            parent::init();
            self::$isInitialized = true;
        }
    }

    /**
     * Initializes infrastructure services and utilities.
     *
     * @return void
     */
    protected static function initServices(): void
    {
        parent::initServices();

        ServiceRegister::registerService(
            ShopLoggerAdapter::CLASS_NAME,
            function () {
                return self::$loggerAdapter;
            }
        );
    }

    /**
     * @return void
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();
    }
}
