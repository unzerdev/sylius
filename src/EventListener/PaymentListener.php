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
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\CancellationRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\ChargeRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\CancellationNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\OrderManagement\Exceptions\ChargeNotPossibleException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\InvalidTransactionHistory;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use UnzerSDK\Exceptions\UnzerApiException;

class PaymentListener
{
    /**
     * @throws ConnectionSettingsNotFoundException
     * @throws TransactionHistoryNotFoundException
     * @throws InvalidTransactionHistory
     * @throws UnzerApiException
     * @throws InvalidCurrencyCode
     * @throws CancellationNotPossibleException
     * @throws UpdateHandlingException
     */
    public function cancelPayment(PaymentInterface $payment): void
    {
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
     * @throws ConnectionSettingsNotFoundException
     * @throws TransactionHistoryNotFoundException
     * @throws InvalidTransactionHistory
     * @throws UnzerApiException
     * @throws ChargeNotPossibleException
     * @throws InvalidCurrencyCode
     * @throws UpdateHandlingException
     */
    public function completePayment(PaymentInterface $payment): void
    {
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
