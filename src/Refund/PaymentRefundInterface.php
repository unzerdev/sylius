<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund;

interface PaymentRefundInterface
{
    public function refund(string $oderId, int $amount = 0): void;
}
