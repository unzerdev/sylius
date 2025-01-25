<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\EventListener;

use Sylius\Bundle\PayumBundle\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethod;
use Sylius\Resource\Exception\UpdateHandlingException;
use SyliusUnzerPlugin\Util\StaticHelper;
use Symfony\Component\Workflow\Event\CompletedEvent;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\CancellationRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\ChargeRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use UnzerSDK\Exceptions\UnzerApiException;
use Webmozart\Assert\Assert;

class PaymentListener implements DisableListenerInterface
{
    use DisableListenerTrait;

    /**
     * @param CompletedEvent $event
     * @return void
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     * @throws UpdateHandlingException
     */
    public function cancelPayment(CompletedEvent $event): void
    {
        $payment = $event->getSubject();
        Assert::isInstanceOf($payment, PaymentInterface::class);
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $payment->getMethod();
        /** @var GatewayConfigInterface $gateway */
        $gateway = $paymentMethod->getGatewayConfig();
        $gatewayName = $gateway->getGatewayName();

        if ($gatewayName !== StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY) {
            return;
        }
        /** @var Order $order */
        $order = $payment->getOrder();
        /** @var ChannelInterface $channel */
        $channel = $order->getChannel();
        $response = AdminAPI::get()->order((string)$channel->getId())->cancel(
            new CancellationRequest(
                (string)$order->getId(), Amount::fromInt(
                $order->getTotal(),
                Currency::fromIsoCode($order->getCurrencyCode())
            )
            )
        );
        if (!$response->isSuccessful()) {
            throw new UpdateHandlingException('Unzer API cancel call failed.');
        }
    }

    /**
     * @param CompletedEvent $event
     * @return void
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     * @throws UpdateHandlingException
     */
    public function completePayment(CompletedEvent $event): void
    {

        $payment = $event->getSubject();
        Assert::isInstanceOf($payment, PaymentInterface::class);
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $payment->getMethod();
        /** @var GatewayConfigInterface $gateway */
        $gateway = $paymentMethod->getGatewayConfig();
        $gatewayName = $gateway->getGatewayName();

        if ($gatewayName !== StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY) {
            return;
        }
        /** @var Order $order */
        $order = $payment->getOrder();

        $details = $payment->getDetails();

        if ($details['unzer']['payment']['status'] === $payment::STATE_COMPLETED) {
            return;
        }
        /** @var ChannelInterface $channel */
        $channel = $order->getChannel();
        $response = AdminAPI::get()->order((string)$channel->getId())->charge(
            new ChargeRequest(
                (string)$order->getId(), Amount::fromInt(
                $order->getTotal(),
                Currency::fromIsoCode($order->getCurrencyCode())
            )
            )
        );
        if (!$response->isSuccessful()) {
           throw new UpdateHandlingException('Unzer API complete call failed.');
        }
    }
}
