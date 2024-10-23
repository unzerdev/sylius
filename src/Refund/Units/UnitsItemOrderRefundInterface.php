<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Units;

use SyliusUnzerPlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;

interface UnitsItemOrderRefundInterface
{
    public function refund(OrderInterface $order, PartialRefundItems $partialRefundItems): array;

    public function getActualRefundedQuantity(OrderInterface $order, int $itemId): int;
}
