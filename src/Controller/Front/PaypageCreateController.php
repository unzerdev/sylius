<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Front;

use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Core\Repository\OrderRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Unzer\Core\BusinessLogic\CheckoutAPI\CheckoutAPI;
use Unzer\Core\BusinessLogic\CheckoutAPI\PaymentPage\Request\PaymentPageCreateRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency;

/**
 * Class PaypageCreateController
 *
 * @package SyliusUnzerPlugin\Controller\Front
 */
class PaypageCreateController extends AbstractController
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly UrlGeneratorInterface $router,
    ) {
    }
    public function create(Request $request): Response
    {
        $orderId = $request->get('orderId');

        /** @var OrderInterface|null $order */
        $order = $this->orderRepository->find($orderId);
        if (null === $order || null === $order->getChannel()) {
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('error', 'sylius_unzer_plugin.checkout.payment_error');

            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        /** @var string $paymentMethodType */
        $paymentMethodType = $request->get('paymentType', '');
        $this->router->getContext()->setScheme('https');
        $response = CheckoutAPI::get()->paymentPage($order->getChannel()->getId())->create(new PaymentPageCreateRequest(
            $paymentMethodType,
            Amount::fromInt($order->getTotal(), Currency::fromIsoCode($order->getCurrencyCode())),
            $this->router->generate('unzer_payment_complete', ['orderId' => $order->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
        ));

        if (!$response->isSuccessful()) {
            $flashBag = $request->getSession()->getBag('flashes');
            $flashBag->add('error', 'sylius_unzer_plugin.checkout.payment_error');

            return new JsonResponse([], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($response->toArray());
    }
}
