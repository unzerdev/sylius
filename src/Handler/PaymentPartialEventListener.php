<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Handler;

use Payum\Core\Payum;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Repository\OrderRepositoryInterface;
use Sylius\RefundPlugin\Event\UnitsRefunded;
use Sylius\RefundPlugin\Model\OrderItemUnitRefund;
use Sylius\RefundPlugin\Model\ShipmentRefund;
use Sylius\RefundPlugin\Provider\OrderRefundedTotalProviderInterface;
use SyliusUnzerPlugin\Handler\Request\RefundOrder;
use SyliusUnzerPlugin\Util\StaticHelper;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Webmozart\Assert\Assert;

final class PaymentPartialEventListener
{
    /** @var OrderRepositoryInterface<OrderInterface> */
    private OrderRepositoryInterface $orderRepository;

    /** @var OrderRefundedTotalProviderInterface */
    private OrderRefundedTotalProviderInterface $orderRefundedTotalProvider;


    /** @var Payum */
    private Payum $payum;

    /**
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     * @param OrderRefundedTotalProviderInterface $orderRefundedTotalProvider
     * @param Payum $payum
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        OrderRefundedTotalProviderInterface $orderRefundedTotalProvider,
        Payum $payum
    ) {
        $this->orderRepository = $orderRepository;
        $this->payum = $payum;
        $this->orderRefundedTotalProvider = $orderRefundedTotalProvider;
    }


    public function __invoke(UnitsRefunded $units): void
    {
        /** @var Order $order */
        $order = $this->orderRepository->findOneBy(['number' => $units->orderNumber()]);

        /** @var PaymentInterface|null $payment */
        $payment = $order->getPayments()->last();
        if (null === $payment) {
            throw new NotFoundHttpException();
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        Assert::notNull($paymentMethod->getGatewayConfig());
        $gateWayName = $paymentMethod->getGatewayConfig()->getGatewayName();

//        if (false === ($gateWayName === StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY)) {
//            return;
//        }

        $details = $payment->getDetails();

        $details['metadata']['refund']['items'] = $units->units();
        $details['metadata']['refund']['shipments'] = $units->shipments();
        $details['metadata']['refund']['refundedTotal'] = ($this->orderRefundedTotalProvider)($order);
        $payment->setDetails($details);

       // $hash = $details['metadata']['refund_token'];

//        /** @var TokenInterface|mixed $token */
//        $token = $this->payum->getTokenStorage()->find($hash);
//
//        if (null === $token || !$token instanceof TokenInterface) {
//            $this->loggerAction->addNegativeLog(sprintf('A token with hash `%s` could not be found.', $hash));
//
//            throw new BadRequestHttpException(sprintf('A token with hash `%s` could not be found.', $hash));
//        }

        $gateway = $this->payum->getGateway('unzer_payment');

        $gateway->execute(new RefundOrder($details));
    }
}
