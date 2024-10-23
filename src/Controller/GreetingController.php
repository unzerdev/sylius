<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use SyliusUnzerPlugin\Refund\PaymentRefundInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\Infrastructure\Logger\Logger;

final class GreetingController extends AbstractController
{


    public function staticallyGreetAction(?string $name): Response
    {
        return $this->render('@SyliusUnzerPlugin/static_greeting.html.twig', ['greeting' => $this->getGreeting($name)]);
    }

    public function dynamicallyGreetAction(PaymentRefundInterface $paymentRefund, string $name): Response
    {

        $paymentRefund->refund('14', 2000);
        Logger::logError('Test error');
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
