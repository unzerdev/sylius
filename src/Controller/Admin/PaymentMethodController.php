<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\EnablePaymentMethodRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\GetPaymentMethodConfigRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\SavePaymentMethodConfigRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Country\Exceptions\InvalidCountryArrayException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidBookingMethodException;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use UnzerSDK\Exceptions\UnzerApiException;

final class PaymentMethodController extends AbstractController
{

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function getPaymentMethodsAction(Request $request): Response
    {
        $methods = AdminAPI::get()->paymentMethods($request->get('storeId'))->getPaymentMethods();

        return $this->json($methods->toArray());
    }

    /**
     * @param string $type
     * @param Request $request
     *
     * @return Response
     *
     */
    public function enablePaymentMethodAction(string $type, Request $request): Response
    {
        $response = AdminAPI::get()->paymentMethods($request->get('storeId'))->enablePaymentMethod(
            new EnablePaymentMethodRequest($type, $request->get('enabled'))
        );

        return $this->json($response->toArray());
    }

    /**
     * @param string $type
     * @param Request $request
     *
     * @return Response
     */
    public function getPaymentMethodConfiguration(string $type, Request $request): Response
    {
        $method = AdminAPI::get()->paymentMethods($request->get('storeId'))->getPaymentConfig(
            new GetPaymentMethodConfigRequest($type)
        );

        return $this->json($method->toArray());
    }

    /**
     * @param string $type
     * @param Request $request
     *
     * @return Response
     *
     * @throws InvalidCountryArrayException
     * @throws InvalidBookingMethodException
     * @throws InvalidTranslatableArrayException
     */
    public function upsertPaymentMethodConfiguration(string $type, Request $request): Response
    {
        $minAmount = $request->get('minOrderAmount');
        $maxAmount = $request->get('maxOrderAmount');
        $surcharge = $request->get('surcharge');

        $response = AdminAPI::get()->paymentMethods($request->get('storeId'))->savePaymentConfig(
            new SavePaymentMethodConfigRequest(
                $type,
                $request->get('bookingMethod'),
                $request->get('name'),
                $request->get('description'),
                $request->get('statusIdToCharge'),
                $minAmount ? (double)$minAmount : null,
                $maxAmount ? (double)$maxAmount : null,
                $surcharge ? (double)$surcharge : null,
                $request->get('restrictedCountries'),
                $request->get('sendBasketData'),
            )
        );

        return $this->json($response->toArray());
    }

}
