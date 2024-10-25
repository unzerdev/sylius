<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Sylius\Bundle\CoreBundle\Controller\PaymentMethodController as BasePaymentMethodController;
use Sylius\Component\Core\Model\PaymentMethod;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use UnzerSDK\Exceptions\UnzerApiException;

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

        if ($paymentMethod->getCode() === 'unzer_payment') {
            return $this->redirectToRoute('unzer_admin_config');
        }

        return parent::updateAction($request);
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws UnzerApiException
     */
    public function deleteAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $this->isGrantedOr403($configuration, ResourceActions::SHOW);

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = $this->findOr404($configuration);

        if ($paymentMethod->getCode() === 'unzer_payment') {

            $channels = $paymentMethod->getChannels();

            foreach ($channels as $channel) {
                $storeId = $channel->getId();

                AdminAPI::get()->disconnect($storeId)->disconnect();
            }
        }

        return parent::deleteAction($request);
    }
}
