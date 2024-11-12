<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Checkout\SelectPayment;

use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Order\Factory\AdjustmentFactoryInterface;
use Sylius\Component\Order\Model\OrderInterface as BaseOrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use SyliusUnzerPlugin\Util\StaticHelper;
use Symfony\Component\HttpFoundation\RequestStack;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request\PaymentMethodsRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use UnzerSDK\Exceptions\UnzerApiException;
use Webmozart\Assert\Assert;

/**
 * Class PaymentSelectionProcessor
 *
 * @package SyliusUnzerPlugin\Services\Checkout\SelectPayment
 */
final class PaymentSelectionProcessor implements OrderProcessorInterface
{
    /**
     * @param RequestStack $requestStack
     * @param AdjustmentFactoryInterface<AdjustmentInterface> $adjustmentFactory
     */
    public function __construct(
        private RequestStack $requestStack,
        private AdjustmentFactoryInterface $adjustmentFactory
    ) {
    }

    /**
     * @throws UnzerApiException
     * @throws InvalidCurrencyCode
     */
    public function process(BaseOrderInterface $order): void
    {
        if (!$order instanceof OrderInterface) {
            return;
        }

        /** @var ?PaymentInterface $payment */
        $payment = $order->getLastPayment(PaymentInterface::STATE_CART);
        if (null === $payment) {
            return;
        }

        /** @var ?PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();
        $this->clearUnzerAdjustment($order);
        if ($paymentMethod?->getCode() !== StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY) {
            $this->clearUnzerDetails($payment);
            $payment->setAmount($order->getTotal());

            return;
        }

        /** @var string $unzerPaymentType */
        $unzerPaymentType = $this->requestStack->getCurrentRequest()?->get('unzer_payment_method_type', '');
        $this->setInUnzerDetails($order, $payment, $unzerPaymentType);
        $payment->setAmount($order->getTotal());
    }

    /**
     * @throws UnzerApiException
     * @throws InvalidCurrencyCode
     */
    private function setInUnzerDetails(OrderInterface $order, PaymentInterface $payment, string $paymentType): void
    {
        $paymentDetails = $payment->getDetails();
        if ('' !== $paymentType) {
            $paymentDetails['unzer']['payment_type'] = $paymentType;
        }

        $response = CheckoutAPI::get()->paymentMethods((string)$order->getChannel()?->getId())
            ->getAvailablePaymentMethods(new PaymentMethodsRequest(
                (string)$order->getBillingAddress()?->getCountryCode(),
                Amount::fromInt($order->getTotal(), Currency::fromIsoCode($order->getCurrencyCode())),
                (string)$order->getLocaleCode()
            ));

        if (!$response->isSuccessful()) {
            return;
        }

        $unzerPaymentTypes = $response->toArray();

        foreach ($unzerPaymentTypes as $configPaymentType) {
            if ($configPaymentType['type'] === $paymentType && array_key_exists('surcharge', $configPaymentType)) {
                $order->addAdjustment($this->adjustmentFactory->createWithData(
                    AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT,
                    StaticHelper::UNZER_PAYMENT_METHOD_SURCHARGE,
                    $surcharge = $configPaymentType['surcharge']['value'],
                    false,
                    ['paymentMethodCode' => StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY]
                ));
                $paymentDetails['unzer']['surcharge'] = $surcharge;

                break;
            }
        }

        $payment->setDetails($paymentDetails);
    }

    private function clearUnzerDetails(PaymentInterface $payment): void
    {
        $paymentDetails = $payment->getDetails();
        unset($paymentDetails['unzer']);
        $payment->setDetails($paymentDetails);
    }

    private function clearUnzerAdjustment(OrderInterface $order): void
    {
        foreach ($order->getAdjustments(AdjustmentInterface::ORDER_PROMOTION_ADJUSTMENT) as $adjustment) {
            $paymentCode = $adjustment->getDetails()['paymentMethodCode'] ?? '';
            if ($paymentCode === StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY) {
                $order->removeAdjustment($adjustment);

                break;
            }
        }
    }
}
