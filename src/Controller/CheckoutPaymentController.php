<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentMethods\Request\PaymentMethodsRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;
use Unzer\Core\Infrastructure\Logger\Logger;

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
        $templateData = [
            'selected_payment_type' => '',
            'payment_types' => [],
        ];

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->find($orderId);
        if (null === $order || null === $order->getChannel()) {
            return $this->render('@SyliusUnzerPlugin/Checkout/SelectPayment/payment_types.html.twig', $templateData);
        }

        $payment = $order->getLastPayment();
        if (
            null !== $payment &&
            $payment->getMethod()?->getCode() === 'unzer_payment'
        ) {
            $templateData['selected_payment_type'] = $payment->getDetails()['unzer']['payment_type'] ?? '';
        }

        try {
            $response = CheckoutAPI::get()->paymentMethods($order->getChannel()->getId())
                ->getAvailablePaymentMethods(new PaymentMethodsRequest(
                    (string)$order->getBillingAddress()?->getCountryCode(),
                    Amount::fromInt($order->getTotal(), Currency::fromIsoCode($order->getCurrencyCode())),
                    (string)$order->getLocaleCode()
                ));

            if ($response->isSuccessful()) {
                $templateData['payment_types'] = $response->toArray();
            }
        } catch (\Exception $e) {
            Logger::logWarning(
                'Exception while fetching available payment methods.',
                'Integration',
                ['exceptionMessage' => $e->getMessage(), 'exceptionTrace' => $e->getTraceAsString()]
            );
        }

        return $this->render('@SyliusUnzerPlugin/Checkout/SelectPayment/payment_types.html.twig', $templateData);
    }
}
