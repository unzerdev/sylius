<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;

/**
 * Class ConfigurationController.
 *
 * @package SyliusUnzerPlugin\Controller
 */
final class ConfigurationController extends AbstractController
{
    /**
     * @param PaymentMethodFactoryInterface $paymentMethodFactory
     * @param PaymentMethodRepositoryInterface $paymentMethodRepository
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(  private PaymentMethodFactoryInterface $paymentMethodFactory,
        private PaymentMethodRepositoryInterface $paymentMethodRepository,
        private ChannelRepositoryInterface $channelRepository)
    {
    }

    /**
     *
     * @return Response
     */
    public function configAction(): Response
    {
        return $this->render('@SyliusUnzerPlugin/config.html.twig');
    }

    public function enableAction(): Response
    {
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => 'unzer_payment']);

        if ($paymentMethod == null) {
            // add payment method in database.
            $paymentMethod = $this->paymentMethodFactory->createWithGateway('unzer_payment');
            $paymentMethod->getGatewayConfig()?->setGatewayName('unzer_payment');
            $paymentMethod->setCode('unzer_payment');
            $paymentMethod->setName('Unzer');
            $this->paymentMethodRepository->add($paymentMethod);
        }

        return $this->redirectToRoute('unzer_admin_config');
    }
}
