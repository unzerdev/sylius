<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use SyliusUnzerPlugin\Handler\Request\RefundOrder;

final class RefundOrderAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;


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
