<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund;

use Mollie\Api\Resources\Order;

interface OrderRefundInterface
{
    public function refund(Order $order): void;
}
