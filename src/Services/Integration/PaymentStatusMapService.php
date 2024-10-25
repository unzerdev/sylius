<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentStatusMap\PaymentStatusMapServiceInterface;

class PaymentStatusMapService implements PaymentStatusMapServiceInterface
{
    /**
     * @inheritDoc
     */
    public function getDefaultPaymentStatusMap(): array
    {
        return [];
    }
}
