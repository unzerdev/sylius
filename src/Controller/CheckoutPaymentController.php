<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CheckoutPaymentController
 *
 * @package SyliusUnzerPlugin\Controller
 */
final class CheckoutPaymentController extends AbstractController
{
    /**
     * CheckoutPaymentController constructor.
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(private readonly OrderRepositoryInterface $orderRepository)
    {
    }

    public function renderPaymentTypes(Request $request): Response
    {
        $orderId = $request->attributes->getInt('orderId');
        $ynzerPaymentType = '';

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->find($orderId);
        if (null === $order) {
            return $this->render('@SyliusUnzerPlugin/Checkout/SelectPayment/payment_types.html.twig', [
                'unzer_payment_method_type' => $ynzerPaymentType
            ]);
        }

        $payment = $order->getLastPayment(PaymentInterface::STATE_CART);
        if (null === $payment || $payment->getMethod()?->getCode() !== 'unzer_payment') {
            return $this->render('@SyliusUnzerPlugin/Checkout/SelectPayment/payment_types.html.twig', [
                'unzer_payment_method_type' => $ynzerPaymentType
            ]);
        }

        // TODO: Call core library to fetch available payment method types
        if (array_key_exists('unzer_payment_method_type', $payment->getDetails())) {
            $ynzerPaymentType = $payment->getDetails()['unzer_payment_method_type'];
        }

        return $this->render('@SyliusUnzerPlugin/Checkout/SelectPayment/payment_types.html.twig', [
            'unzer_payment_method_type' => $ynzerPaymentType
        ]);
    }
}
