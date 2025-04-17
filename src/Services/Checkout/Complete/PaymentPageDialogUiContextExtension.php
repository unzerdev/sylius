<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Checkout\Complete;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 *
 */
class PaymentPageDialogUiContextExtension extends AbstractExtension
{
    public function __construct(
        private UrlGeneratorInterface $router,
        private CartContextInterface $cartContext,
        private RequestStack $requestStack,
        private OrderRepositoryInterface $orderRepository,
    ) {
    }

    public function unzerProviderData(): array
    {
        $templateContext = [];

        /** @var OrderInterface $order */
        $order = $this->cartContext->getCart();
        $orderId = $order->getId();

        if (null === $orderId) {
            $orderId = $this->getOrderIdFromRequest();
        }

        $paymentDetails = [];
        $unzerPaymentType = '';

        $payment = $order->getLastPayment();
        if (null !== $payment && $payment->getMethod()?->getCode() === 'unzer_payment') {
            $paymentDetails = $payment->getDetails();
        }


        if ($order->canBeProcessed()) {
            $unzerPaymentType = $paymentDetails['unzer']['payment_type'] ?? '';;
        }

        $templateContext['unzer_payment_page_url'] = $this->router->generate(
            'unzer_paypage_create',
            ['orderId' => $orderId]
        );
        $templateContext['unzer_payment_error'] = $this->router->generate('unzer_payment_error');
        $templateContext['unzer_payment_page_type'] = $unzerPaymentType;

        return $templateContext;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unzer_provider_data', [$this, 'unzerProviderData']),
        ];
    }

    /**
     * @return ?int
     */
    private function getOrderIdFromRequest(): ?int
    {

        $request = $this->requestStack->getCurrentRequest();
        $tokenValue = $request->get('tokenValue') ?? null;

        if (null !== $tokenValue) {
            $order = $this->orderRepository->findOneByTokenValue($tokenValue);

            return $order->getId();
        }

        return null;
    }
}
