<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;
use Sylius\RefundPlugin\Provider\OrderRefundedTotalProviderInterface;
use SyliusUnzerPlugin\Handler\Request\RefundOrder;

final class RefundOrderAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;


    public function execute($request): void
    {
        $a =1;
    }

    public function convert(array $data): int
    {
        $value = 0;

        foreach ($data as $items) {
            foreach ($this->getTotal($items) as $total) {
                $value += $total;
            }
        }

        return $value;
    }

    private function getTotal(array $refundsData): iterable
    {
        /** @var OrderItemUnitRefund|ShipmentRefund $refundData */
        foreach ($refundsData as $refundData) {
            yield $refundData->total();
        }
    }

    public function supports($request): bool
    {
        return
            $request instanceof RefundOrder &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
