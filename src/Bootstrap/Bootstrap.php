<?php

namespace SyliusUnzerPlugin\Bootstrap;

use Doctrine\ORM\EntityManagerInterface;
use SyliusUnzerPlugin\Repositories\BaseRepository;
use SyliusUnzerPlugin\Services\Integration\EncryptorService;
use SyliusUnzerPlugin\Services\Integration\WebhookUrlService;
use Unzer\Core\BusinessLogic\BootstrapComponent;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;
use Unzer\Core\Infrastructure\Configuration\ConfigEntity;
use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\ORM\Exceptions\RepositoryClassException;
use Unzer\Core\Infrastructure\ORM\RepositoryRegistry;
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
     * @var EntityManagerInterface
     */
    private static EntityManagerInterface $entityManager;

    /**
     * @param ShopLoggerAdapter $loggerAdapter
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(ShopLoggerAdapter $loggerAdapter, EntityManagerInterface $entityManager)
    {
        self::$loggerAdapter = $loggerAdapter;
        self::$entityManager = $entityManager;
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

        ServiceRegister::registerService(
            EncryptorInterface::class,
            function () {
                return new EncryptorService();
            }
        );

        ServiceRegister::registerService(
            WebhookUrlServiceInterface::class,
            function () {
                return new WebhookUrlService();
            }
        );
    }

    /**
     * @return void
     *
     * @throws RepositoryClassException
     */
    protected static function initRepositories(): void
    {
        parent::initRepositories();

        BaseRepository::setEntityManager(self::$entityManager);

        RepositoryRegistry::registerRepository(ConfigEntity::getClassName(), BaseRepository::getClassName());
        RepositoryRegistry::registerRepository(ConnectionSettings::getClassName(), BaseRepository::getClassName());
        RepositoryRegistry::registerRepository(WebhookData::getClassName(), BaseRepository::getClassName());
        RepositoryRegistry::registerRepository(PaymentPageSettings::getClassName(), BaseRepository::getClassName());
    }
}
