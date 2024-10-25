<?php

namespace SyliusUnzerPlugin\Bootstrap;

use Doctrine\ORM\EntityManagerInterface;
use SyliusUnzerPlugin\Repositories\BaseRepository;
use SyliusUnzerPlugin\Services\Integration\CountryService;
use SyliusUnzerPlugin\Services\Integration\CurrencyService;
use SyliusUnzerPlugin\Services\Integration\EncryptorService;
use SyliusUnzerPlugin\Services\Integration\ImageHandlerService;
use SyliusUnzerPlugin\Services\Integration\LanguageService;
use SyliusUnzerPlugin\Services\Integration\OrderService;
use SyliusUnzerPlugin\Services\Integration\PaymentStatusMapService;
use SyliusUnzerPlugin\Services\Integration\StoreService;
use SyliusUnzerPlugin\Services\Integration\VersionService;
use SyliusUnzerPlugin\Services\Integration\WebhookUrlService;
use Unzer\Core\BusinessLogic\BootstrapComponent;
use Unzer\Core\BusinessLogic\DataAccess\Connection\Entities\ConnectionSettings;
use Unzer\Core\BusinessLogic\DataAccess\PaymentMethodConfig\Entities\PaymentMethodConfig;
use Unzer\Core\BusinessLogic\DataAccess\PaymentPageSettings\Entities\PaymentPageSettings;
use Unzer\Core\BusinessLogic\DataAccess\TransactionHistory\Entities\TransactionHistory;
use Unzer\Core\BusinessLogic\DataAccess\Webhook\Entities\WebhookData;
use Unzer\Core\BusinessLogic\Domain\Integration\Language\LanguageService as LanguageServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Country\CountryService as CountryServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentStatusMap\PaymentStatusMapServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService;
use Unzer\Core\BusinessLogic\Domain\Integration\Versions\VersionService as VersionServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Store\StoreService as StoreServiceInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Currency\CurrencyServiceInterface;
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
     * @var LanguageService
     */
    private static LanguageService $languageService;

    /**
     * @var CountryService
     */
    private static CountryService $countryService;

    /**
     * @var StoreService
     */
    private static StoreService $storeService;

    /**
     * @var WebhookUrlService
     */
    private static WebhookUrlService $webhookUrlService;

    /**
     * @var EncryptorService
     */
    private static EncryptorService $encryptorService;

    /**
     * @var CurrencyService
     */
    private static CurrencyService $currencyService;

    /**
     * @var ImageHandlerService
     */
    private static ImageHandlerService $imageHandlerService;

    /**
     * @var OrderServiceInterface
     */
    private static OrderServiceInterface $orderService;

    /**
     * @param ShopLoggerAdapter $loggerAdapter
     * @param EntityManagerInterface $entityManager
     * @param LanguageService $languageService
     * @param CountryService $countryService
     * @param StoreService $storeService
     * @param WebhookUrlService $webhookUrlService
     * @param EncryptorService $encryptorService
     * @param CurrencyService $currencyService
     * @param ImageHandlerService $imageHandlerService
     * @param OrderServiceInterface $orderService
     */
    public function __construct(
        ShopLoggerAdapter $loggerAdapter,
        EntityManagerInterface $entityManager,
        LanguageService $languageService,
        CountryService $countryService,
        StoreService $storeService,
        WebhookUrlService $webhookUrlService,
        EncryptorService $encryptorService,
        CurrencyService $currencyService,
        ImageHandlerService $imageHandlerService,
        OrderServiceInterface $orderService
    ) {
        self::$loggerAdapter = $loggerAdapter;
        self::$entityManager = $entityManager;
        self::$languageService = $languageService;
        self::$countryService = $countryService;
        self::$storeService = $storeService;
        self::$webhookUrlService = $webhookUrlService;
        self::$encryptorService = $encryptorService;
        self::$currencyService = $currencyService;
        self::$imageHandlerService = $imageHandlerService;
        self::$orderService = $orderService;
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
                return self::$encryptorService;
            }
        );

        ServiceRegister::registerService(
            WebhookUrlServiceInterface::class,
            function () {
                return self::$webhookUrlService;
            }
        );

        ServiceRegister::registerService(
            LanguageServiceInterface::class,
            function () {
                return self::$languageService;
            }
        );

        ServiceRegister::registerService(
            CountryServiceInterface::class,
            function () {
                return self::$countryService;
            }
        );

        ServiceRegister::registerService(
            VersionServiceInterface::class,
            function () {
                return new VersionService();
            }
        );

        ServiceRegister::registerService(
            StoreServiceInterface::class,
            function () {
                return self::$storeService;
            }
        );

        ServiceRegister::registerService(
            CurrencyServiceInterface::class,
            function () {
                return self::$currencyService;
            }
        );

        ServiceRegister::registerService(
            UploaderService::class,
            function () {
                return self::$imageHandlerService;
            }
        );

        ServiceRegister::registerService(
            PaymentStatusMapServiceInterface::class,
            function () {
                return new PaymentStatusMapService();
            }
        );

        ServiceRegister::registerService(
            OrderServiceInterface::class,
            function () {
                return self::$orderService;
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
        RepositoryRegistry::registerRepository(PaymentMethodConfig::getClassName(), BaseRepository::getClassName());
        RepositoryRegistry::registerRepository(TransactionHistory::getClassName(), BaseRepository::getClassName());
    }
}
