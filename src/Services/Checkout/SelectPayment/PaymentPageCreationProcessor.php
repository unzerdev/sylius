<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Checkout\SelectPayment;

use SM\Factory\FactoryInterface;
use Sylius\Bundle\CoreBundle\Provider\FlashBagProvider;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\OrderCheckoutTransitions;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Webmozart\Assert\Assert;

/**
 * Class PaymentPageCreationProcessor
 *
 * @package SyliusUnzerPlugin\Services\Checkout\SelectPayment
 */
final class PaymentPageCreationProcessor implements OrderProcessorInterface
{
    public function __construct(
        private readonly UrlGeneratorInterface $router,
        private readonly RequestStack $requestStack,
        private readonly FactoryInterface $stateMachineFactory
    ) {
    }

    public function process(BaseOrderInterface $order): void
    {
        Assert::isInstanceOf($order, OrderInterface::class);

        /** @var OrderInterface $order */
        if (!$order->canBeProcessed() || null === $order->getChannel()) {
            return;
        }

        $payment = $order->getLastPayment(PaymentInterface::STATE_CART);
        if (null === $payment || $payment->getMethod()?->getCode() !== 'unzer_payment') {
            return;
        }

        $paymentDetails = $payment->getDetails();
        if (
            !array_key_exists('unzer', $paymentDetails) ||
            !array_key_exists('payment_type', $paymentDetails['unzer'])
        ) {
            return;
        }

        if (
            array_key_exists('payment_page', $paymentDetails['unzer']) &&
            array_key_exists('errorMessage', $paymentDetails['unzer']['payment_page'])
        ) {
            unset($paymentDetails['unzer']['payment_page']);
            $payment->setDetails($paymentDetails);

            return;
        }

        $this->router->getContext()->setScheme('https');
        $response = CheckoutAPI::get()->paymentPage($order->getChannel()->getId())->create(new PaymentPageCreateRequest(
            $paymentDetails['unzer']['payment_type'],
            Amount::fromInt($order->getTotal(), Currency::fromIsoCode($order->getCurrencyCode())),
            $this->router->generate('unzer_payment_complete', ['orderId' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));

        $paymentDetails['unzer']['payment_page'] = $response->toArray();
        $payment->setDetails($paymentDetails);

        if ($response->isSuccessful()) {
            return;
        }

        FlashBagProvider::getFlashBag($this->requestStack)
            ->add('error', 'sylius_unzer_plugin.checkout.payment_error');

        $stateMachine = $this->stateMachineFactory->get($order, OrderCheckoutTransitions::GRAPH);
        $stateMachine->apply(OrderCheckoutTransitions::TRANSITION_SELECT_SHIPPING);
    }
}
