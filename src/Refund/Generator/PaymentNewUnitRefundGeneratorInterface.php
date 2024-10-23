<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Generator;

use SyliusUnzerPlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;

interface PaymentNewUnitRefundGeneratorInterface
{
    public function generate(OrderInterface $order, PartialRefundItems $partialRefundItems): PartialRefundItems;
}
