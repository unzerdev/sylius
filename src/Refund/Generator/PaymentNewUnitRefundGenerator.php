<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Generator;

use SyliusUnzerPlugin\DTO\PartialRefundItem;
use SyliusUnzerPlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;

final class PaymentNewUnitRefundGenerator implements PaymentNewUnitRefundGeneratorInterface
{
    public function generate(OrderInterface $order, PartialRefundItems $partialRefundItems): PartialRefundItems
    {
        $units = $order->getItemUnits();

        foreach ($units as $unit) {
            $partialRefundItem = $partialRefundItems->findById($unit->getId());

            if (null !== $partialRefundItem) {
                $partialRefundItem->setAmountTotal($unit->getTotal());

                continue;
            }

            $partialRefundItem = new PartialRefundItem();

            $partialRefundItem->setId($unit->getId());
            $partialRefundItem->setAmountRefunded(0);
            $partialRefundItem->setAmountTotal($unit->getTotal());

            $partialRefundItems->setPartialRefundItems($partialRefundItem);
        }

        return $partialRefundItems;
    }
}
