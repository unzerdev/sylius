<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Front;

use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Sylius\Component\Payment\Model\PaymentInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;

/**
 * Class PaypageCreateController
 *
 * @package SyliusUnzerPlugin\Controller\Front
 */
class PaypageCreateController extends AbstractController
{
    /**
     * PaypageCreateController constructor.
     *
     * @param OrderRepositoryInterface<OrderInterface> $orderRepository
     */
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UrlGeneratorInterface $router,
        private readonly ObjectManager $orderManager,
    ) {
    }

    /**
     * @throws InvalidCurrencyCode
     */
    public function create(Request $request): Response
    {
        $orderId = $request->get('orderId');

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->find($orderId);
        if (null === $order || null === $order->getChannel()) {
            return $this->getErrorResponse($request);
        }

        $payment = $order->getLastPayment();
        if (
            !in_array($payment?->getState(), [PaymentInterface::STATE_CART, PaymentInterface::STATE_NEW], true) ||
            $payment->getMethod()?->getCode() !== 'unzer_payment'
        ) {
            return $this->getErrorResponse($request);
        }

        /** @var string $paymentMethodType */
        $paymentMethodType = $request->get('paymentType', '');
        if ('' !== $paymentMethodType) {
            $this->assignPaymentTypeToPayment($payment, $paymentMethodType);
        }

        $this->router->getContext()->setScheme('https');
        $response = CheckoutAPI::get()->paymentPage($order->getChannel()->getId())->create(new PaymentPageCreateRequest(
            $paymentMethodType,
            (string)$order->getId(),
            Amount::fromInt($order->getTotal(), Currency::fromIsoCode($order->getCurrencyCode())),
            $this->router->generate('unzer_payment_complete', ['orderId' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));

        if (!$response->isSuccessful()) {
            return $this->getErrorResponse($request);
        }

        return new JsonResponse($response->toArray());
    }

    private function assignPaymentTypeToPayment(PaymentInterface $payment, string $paymentMethodType): void
    {
        $paymentDetails = $payment->getDetails();
        $paymentDetails['unzer']['payment_type'] = $paymentMethodType;
        $payment->setDetails($paymentDetails);
        $this->orderManager->flush();
    }

    private function getErrorResponse(Request $request): JsonResponse
    {
        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', 'sylius_unzer_plugin.checkout.payment_error');

        return new JsonResponse([], Response::HTTP_BAD_REQUEST);
    }
}
