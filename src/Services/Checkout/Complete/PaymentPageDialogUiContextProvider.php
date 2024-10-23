<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Checkout\Complete;

use Sylius\Bundle\UiBundle\ContextProvider\ContextProviderInterface;
use Sylius\Bundle\UiBundle\Registry\TemplateBlock;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class PaymentPageDialogUiContextProvider
 *
 * @package SyliusUnzerPlugin\Services\Checkout\Complete
 */
class PaymentPageDialogUiContextProvider implements ContextProviderInterface
{
    public function __construct(private readonly UrlGeneratorInterface $router)
    {
    }

    public function provide(array $templateContext, TemplateBlock $templateBlock): array
    {
        /** @var OrderInterface $order */
        $order = $templateContext['order'];

        $paymentDetails = [];
        $unzerPaymentType = '';

        $payment = $order->getLastPayment();
        if (null !== $payment && $payment->getMethod()?->getCode() === 'unzer_payment' ) {
            $paymentDetails = $payment->getDetails();
        }


        if ($order->canBeProcessed()) {
            $unzerPaymentType = $paymentDetails['unzer']['payment_type'] ?? '';;
        }

        $templateContext['unzer_payment_page_url'] = $this->router->generate('unzer_paypage_create', ['orderId' => $order->getId()]);
        $templateContext['unzer_payment_page_type'] = $unzerPaymentType;

        return $templateContext;
    }

    public function supports(TemplateBlock $templateBlock): bool
    {
        return 'unzer_payment_page_dialog' === $templateBlock->getName();
    }
}
