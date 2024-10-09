<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodChecker;
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
     * @var UnzerPaymentMethodChecker
     */
    private UnzerPaymentMethodChecker $unzerPaymentMethodChecker;

    /**
     * @param UnzerPaymentMethodCreator $paymentMethodCreator
     * @param UnzerPaymentMethodChecker $unzerPaymentMethodChecker
     */
    public function __construct(
        UnzerPaymentMethodCreator $paymentMethodCreator,
        UnzerPaymentMethodChecker $unzerPaymentMethodChecker
    ) {
        $this->paymentMethodCreator = $paymentMethodCreator;
        $this->unzerPaymentMethodChecker = $unzerPaymentMethodChecker;
    }

    /**
     *
     * @return Response
     */
    public function configAction(): Response
    {
        if (!$this->unzerPaymentMethodChecker->exists()) {
            return $this->redirectToRoute('sylius_admin_payment_method_index');
        }
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
