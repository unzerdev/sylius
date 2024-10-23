<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund;

use Sylius\RefundPlugin\Command\RefundUnits;

interface PaymentRefundCommandCreatorInterface
{
    public function fromOderAndAmount(string $orderId, int $amount): RefundUnits;
}
