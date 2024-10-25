<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use App\Entity\Payment\PaymentMethod;
use Sylius\Bundle\CoreBundle\Controller\PaymentMethodController as BasePaymentMethodController;
use Sylius\Component\Channel\Model\Channel;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PaymentMethodController.
 *
 * @package SyliusUnzerPlugin\Controller
 */
class PaymentMethodController extends BasePaymentMethodController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function updateAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->findOr404($configuration);

        if ($paymentMethod->getCode() !== null && $paymentMethod->getCode() === 'unzer_payment') {
            return $this->redirectToRoute('unzer_admin_config');
        }

        return parent::updateAction($request);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function deleteAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->findOr404($configuration);

        if ($paymentMethod->getCode() !== null && $paymentMethod->getCode() === 'unzer_payment') {
           //TODO: DELETE ALL DATA
        }

        return parent::deleteAction($request);
    }
}
