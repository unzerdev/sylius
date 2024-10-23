<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Checkout\Complete;

use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

/**
 * Class PaymentPageDialogUiContextProvider
 *
 * @package SyliusUnzerPlugin\Services\Checkout\Complete
 */
class PaymentPageDialogUiContextProvider implements ContextProviderInterface
{

    public function provide(array $templateContext, TemplateBlock $templateBlock): array
    {
        /** @var OrderInterface $order */
        $order = $templateContext['order'];
        if (!$order->canBeProcessed()) {
            return $templateContext;
        }

        $payment = $order->getLastPayment(PaymentInterface::STATE_CART);
        if (null === $payment || $payment->getMethod()?->getCode() !== 'unzer_payment' ) {
            return $templateContext;
        }

        $paymentDetails = $payment->getDetails();
        if (
            !array_key_exists('unzer', $paymentDetails) ||
            !array_key_exists('payment_page', $paymentDetails['unzer'])
        ) {
            return $templateContext;
        }

        $templateContext['unzer_payment_page'] = $payment->getDetails()['unzer']['payment_page'];

        return $templateContext;
    }

    public function supports(TemplateBlock $templateBlock): bool
    {
        return 'sylius.shop.checkout.complete.before_navigation' === $templateBlock->getEventName()
            && 'unzer_payment_page_dialog' === $templateBlock->getName();
    }
}
