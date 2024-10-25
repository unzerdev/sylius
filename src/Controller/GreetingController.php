<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use SM\Factory\Factory;
use SM\Factory\FactoryInterface;
use SyliusUnzerPlugin\EventListener\Event\DisableListenerEvent;
use SyliusUnzerPlugin\Refund\PaymentRefundInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;

final class GreetingController extends AbstractController
{


    public function staticallyGreetAction(?string $name): Response
    {
        return $this->render('@SyliusUnzerPlugin/static_greeting.html.twig', ['greeting' => $this->getGreeting($name)]);
    }

    public function dynamicallyGreetAction(PaymentRefundInterface $paymentRefund, string $name,
        EventDispatcherInterface $eventDispatcher, Factory $factory): Response
    {
        $eventDispatcher->dispatch(new DisableListenerEvent(), DisableListenerEvent::WEBHOOKS);

        $paymentRefund->refund('52', 10000000000000);


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
