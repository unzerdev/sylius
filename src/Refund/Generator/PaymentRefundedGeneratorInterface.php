<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund\Generator;

use SyliusUnzerPlugin\DTO\PartialRefundItems;
use Sylius\Component\Core\Model\OrderInterface;

interface PaymentRefundedGeneratorInterface
{
    public function generate(OrderInterface $order): PartialRefundItems;
}
