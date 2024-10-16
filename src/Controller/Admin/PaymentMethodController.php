<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use UnzerSDK\Exceptions\UnzerApiException;

final class PaymentMethodController extends AbstractController
{

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws UnzerApiException
     */
    public function getPaymentMethodsAction(Request $request): Response
    {

        $methods = AdminAPI::get()->paymentMethods('1')->getPaymentMethods();

        return $this->json($methods->toArray());
    }
}
