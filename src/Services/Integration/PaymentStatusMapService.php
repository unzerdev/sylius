<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Sylius\Component\Payment\Model\PaymentInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\PaymentStatusMap\PaymentStatusMapServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentStatusMap\Enums\PaymentStatus;

/**
 * Class PaymentStatusMapService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class PaymentStatusMapService implements PaymentStatusMapServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getDefaultPaymentStatusMap(): array
    {
        return self::defaultStatusMap();
    }

    /**
     * @return array
     */
    public static function defaultStatusMap(): array
    {
        return [
            PaymentStatus::PAID => PaymentInterface::STATE_COMPLETED,
            PaymentStatus::UNPAID => PaymentInterface::STATE_NEW,
            PaymentStatus::FULL_REFUND => PaymentInterface::STATE_REFUNDED,
            PaymentStatus::CANCELLED => PaymentInterface::STATE_CANCELLED,
            PaymentStatus::COLLECTION => PaymentInterface::STATE_PROCESSING,
            PaymentStatus::PARTIAL_REFUND => PaymentInterface::STATE_REFUNDED,
            PaymentStatus::DECLINED => PaymentInterface::STATE_FAILED
        ];
    }
}
