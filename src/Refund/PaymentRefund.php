<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Refund;

use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class PaymentRefund implements PaymentRefundInterface
{
    /** @var MessageBusInterface */
    private MessageBusInterface $commandBus;

    /** @var PaymentRefundCommandCreatorInterface */
    private PaymentRefundCommandCreatorInterface $commandCreator;


    public function __construct(
        MessageBusInterface $commandBus,
        PaymentRefundCommandCreatorInterface $commandCreator
    ) {
        $this->commandBus = $commandBus;
        $this->commandCreator = $commandCreator;
    }

    public function refund(string $oderId, int $amount = 0): void
    {
        try {
            $refundUnits = $this->commandCreator->fromOderAndAmount($oderId, $amount);
            $this->commandBus->dispatch($refundUnits);
        } catch (HandlerFailedException $e) {
            // log error
        }
    }
}
