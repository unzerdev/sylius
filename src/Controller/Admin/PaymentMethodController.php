<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;

final class PaymentMethodController extends AbstractController
{

    /**
     * @param Request $request
     *
     * @return Response
     *
     */
    public function getPaymentMethodsAction(Request $request): Response
    {
        return $this->json(AdminAPI::get()->paymentMethods($request->get("storeId"))->getPaymentMethods()->toArray());
    }
}
