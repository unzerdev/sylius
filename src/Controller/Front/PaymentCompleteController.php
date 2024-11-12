<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Front;

use Doctrine\Persistence\ObjectManager;
use Payum\Core\Payum;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\CoreBundle\Provider\FlashBagProvider;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class PaymentCompleteController
 *
 * @package SyliusUnzerPlugin\Controller\Front
 */
class PaymentCompleteController extends AbstractController
{
    /**
     * CheckoutPaymentController constructor.
     *
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private Payum $payum,
        private FactoryInterface $stateMachineFactory,
        private ObjectManager $orderManager
    ) {
    }

    /**
     * @param Request $request
     * @param SessionInterface $session
     *
     * @return Response
     */
    public function process(Request $request, SessionInterface $session): Response
    {
        $orderId = $request->get('orderId');
        $session->set('sylius_order_id', $orderId);

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->find($orderId);
        if (null === $order) {
            FlashBagProvider::getFlashBag($session)
                ->add('error', 'sylius_unzer_plugin.checkout.payment_error');

            return $this->redirectToRoute('sylius_shop_checkout_select_payment');
        }

        $payment = $order->getLastPayment();
        if (null === $payment) {
            FlashBagProvider::getFlashBag($session)
                ->add('error', 'sylius_unzer_plugin.checkout.payment_error');

            return $this->redirectToRoute('sylius_shop_checkout_select_payment');
        }

        if ($order->canBeProcessed()) {
            $this->completeCheckout($order);
        }

        // We can always make authorize token since after unzer processing transaction is already captured or authorized
        // it is just needed to go through sylius checkout payment processing and set adequate status on sylius payment
        $token = $this->payum->getTokenFactory()->createAuthorizeToken(
            'unzer_payment',
            $payment,
            'sylius_shop_order_after_pay',
            [],
        );

        return new RedirectResponse($token->getTargetUrl());
    }

    private function completeCheckout(OrderInterface $order): void
    {
        $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);
        $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_COMPLETE);
        $this->orderManager->flush();
    }
}
