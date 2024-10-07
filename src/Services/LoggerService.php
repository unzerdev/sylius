<?php

namespace SyliusUnzerPlugin\Services;

use Psr\Log\LoggerInterface;
use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\Logger\LogData;

/**
 * Class LoggerService.
 *
 * @package SyliusUnzerPlugin\Services
 */
class LoggerService implements ShopLoggerAdapter
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var Configuration
     */
    private Configuration $configuration;

    /**
     * @param LoggerInterface $logger
     * @param Configuration $configService
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
      //  $this->configuration = $configService;
    }

    /**
     * @param LogData $data
     *
     * @return void
     */
    public function logMessage(LogData $data): void
    {

        $this->logger->error('Test error Unzer log');
    }
}
