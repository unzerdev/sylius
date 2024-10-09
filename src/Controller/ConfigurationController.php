<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ConfigurationController extends AbstractController
{

    /**
     * @var UnzerPaymentMethodCreator
     */
    private UnzerPaymentMethodCreator $paymentMethodCreator;


    /**
     * @param UnzerPaymentMethodCreator $paymentMethodCreator
     */
    public function __construct(UnzerPaymentMethodCreator $paymentMethodCreator)
    {
        $this->paymentMethodCreator = $paymentMethodCreator;
    }

    /**
     *
     * @return Response
     */
    public function configAction(): Response
    {
        return $this->render('@SyliusUnzerPlugin/config.html.twig');
    }

    /**
     * @return Response
     */
    public function enableAction(): Response
    {
        $this->paymentMethodCreator->createIfNotExists();
        return $this->redirectToRoute('unzer_admin_config');
    }
}
