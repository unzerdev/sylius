<?php

namespace SyliusUnzerPlugin\Services;

use Psr\Log\LoggerInterface;
use SyliusUnzerPlugin\Repositories\BaseRepository;
use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\Logger\LogData;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;

/**
 * Class LoggerService.
 *
 * @package SyliusUnzerPlugin\Services
 */
class LoggerService implements ShopLoggerAdapter
{
    /**
     * Log level names for corresponding log level codes.
     *
     * @var array
     */
    protected static array $logLevelName = [
        Logger::ERROR => 'ERROR',
        Logger::WARNING => 'WARNING',
        Logger::INFO => 'INFO',
        Logger::DEBUG => 'DEBUG'
    ];

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
    public function __construct(LoggerInterface $logger, Configuration $configService)
    {
        $this->logger = $logger;
        $this->configuration = $configService;
    }

    /**
     * @param LogData $data
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function logMessage(LogData $data): void
    {
        $minLogLevel = $this->configuration->getMinLogLevel();
        $logLevel = $data->getLogLevel();

        if (($logLevel > $minLogLevel) && !$this->configuration->isDebugModeEnabled()) {
            return;
        }

        $message = 'UNZER LOG:' . ' | '
            . 'Date: ' . date('d/m/Y') . ' | '
            . 'Time: ' . date('H:i:s') . ' | '
            . 'Log level: ' . self::$logLevelName[$logLevel] . ' | '
            . 'Message: ' . $data->getMessage();
        $message .= "\n";

        $contextData = [];
        $context = $data->getContext();
        if (!empty($context)) {
            foreach ($context as $item) {
                $contextData[$item->getName()] = $item->getValue();
            }
        }

        switch ($logLevel) {
            case Logger::ERROR:
                $this->logger->error($message, $contextData);
                break;
            case Logger::WARNING:
                $this->logger->warning($message, $contextData);
                break;
            case Logger::INFO:
                $this->logger->info($message, $contextData);
                break;
            case Logger::DEBUG:
                $this->logger->warning($message, $contextData);
        }
    }
}
