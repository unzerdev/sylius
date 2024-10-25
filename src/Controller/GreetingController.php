<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Sylius\Component\Currency\Model\Currency;
use SyliusUnzerPlugin\Refund\PaymentRefundInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\CancellationRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\ChargeRequest;
use Unzer\Core\BusinessLogic\AdminAPI\OrderManagement\Request\RefundRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\EnablePaymentMethodRequest;
use Unzer\Core\BusinessLogic\Domain\Checkout\Models\Amount;
use Unzer\Core\BusinessLogic\Domain\Integration\Order\OrderServiceInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Enums\PaymentMethodTypes;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\ServiceRegister;

final class GreetingController extends AbstractController
{


    public function staticallyGreetAction(?string $name): Response
    {
        return $this->render('@SyliusUnzerPlugin/static_greeting.html.twig', ['greeting' => $this->getGreeting($name)]);
    }

    public function dynamicallyGreetAction(PaymentRefundInterface $paymentRefund, string $name): Response
    {
        /** @var OrderServiceInterface $service */
        $service = ServiceRegister::getService(OrderServiceInterface::class);
        $ammount = $service->getRefundedAmountForOrder('38');
        $paymentRefund->refund('14', 2000);
        AdminAPI::get()->paymentMethods('1')->enablePaymentMethod(new EnablePaymentMethodRequest(PaymentMethodTypes::CARDS, true));

        AdminAPI::get()->order('1')->refund(new RefundRequest('1', Amount::fromFloat(1, \Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency::getDefault())));
        AdminAPI::get()->order('1')->cancel(new CancellationRequest('1', Amount::fromFloat(1, \Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency::getDefault())));
        AdminAPI::get()->order('1')->charge(new ChargeRequest('1', Amount::fromFloat(1, \Unzer\Core\BusinessLogic\Domain\Checkout\Models\Currency::getDefault())));


        return $this->render('@SyliusUnzerPlugin/dynamic_greeting.html.twig',
            ['greeting' => $this->getGreeting($name)]);
    }

    private function getGreeting(?string $name): string
    {
        switch ($name) {
            case null:
                return 'Hello!';
            case 'Lionel Richie':
                return 'Hello, is it me you\'re looking for?';
            default:
                return sprintf('Hello, %s!', $name);
        }
    }
}
