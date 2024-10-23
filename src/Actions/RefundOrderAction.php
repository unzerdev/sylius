<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Actions;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Sylius\RefundPlugin\Provider\OrderRefundedTotalProviderInterface;
use SyliusUnzerPlugin\Handler\Request\RefundOrder;

final class RefundOrderAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    private OrderRefundedTotalProviderInterface $orderRefundedTotalProvider;

    /**
     * @param OrderRefundedTotalProviderInterface $orderRefundedTotalProvider
     */
    public function __construct(OrderRefundedTotalProviderInterface $orderRefundedTotalProvider)
    {
        $this->orderRefundedTotalProvider = $orderRefundedTotalProvider;
    }


    public function execute($request): void
    {

    }

    public function supports($request): bool
    {
        return
            $request instanceof RefundOrder &&
            $request->getModel() instanceof \ArrayAccess;
    }
}
