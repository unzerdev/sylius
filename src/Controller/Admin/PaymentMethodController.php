<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\EnablePaymentMethodRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\GetPaymentMethodConfigRequest;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentMethods\Request\SavePaymentMethodConfigRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Country\Exceptions\InvalidCountryArrayException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidAmountsException;
use Unzer\Core\BusinessLogic\Domain\PaymentMethod\Exceptions\InvalidBookingMethodException;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class PaymentMethodController
 *
 * @package SyliusUnzerPlugin\Controller\Admin
 */
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
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        $methods = AdminAPI::get()->paymentMethods($storeId)->getPaymentMethods();

        return $this->json($methods->toArray());
    }

    /**
     * @param string $type
     * @param Request $request
     *
     * @return Response
     *
     * @throws InvalidAmountsException
     */
    public function enablePaymentMethodAction(string $type, Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        $response = AdminAPI::get()->paymentMethods($storeId)->enablePaymentMethod(
            new EnablePaymentMethodRequest($type, (bool)$request->getPayload()->get('enabled'))
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
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        $method = AdminAPI::get()->paymentMethods($storeId)->getPaymentConfig(
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
     * @throws InvalidTranslatableArrayException|InvalidAmountsException
     */
    public function upsertPaymentMethodConfiguration(string $type, Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');


        $response = AdminAPI::get()->paymentMethods($storeId)->savePaymentConfig(
            $this->createSavePaymentMethodConfigRequest($type, $request)
        );

        return $this->json($response->toArray());
    }

    /**
     * @param string $type
     * @param Request $request
     *
     * @return SavePaymentMethodConfigRequest
     */
    private function createSavePaymentMethodConfigRequest(
        string $type,
        Request $request
    ): SavePaymentMethodConfigRequest {

        /** @var float|null $minAmount */
        $minAmount = is_numeric($request->getPayload()->get('minOrderAmount')) ? (float) $request->getPayload()->get('minOrderAmount') : null;

        /** @var float|null $maxAmount */
        $maxAmount = is_numeric($request->getPayload()->get('maxOrderAmount')) ? (float) $request->getPayload()->get('maxOrderAmount') : null;

        /** @var float|null $surcharge */
        $surcharge = is_numeric($request->getPayload()->get('surcharge')) ? (float) $request->getPayload()->get('surcharge') : null;

        /** @var string $bookingMethod */
        $bookingMethod = $request->getPayload()->get('bookingMethod', '');

        /** @var array $name */
        $name = $request->toArray()['name'] ?? [];

        /** @var array $description */
        $description = $request->toArray()['description'] ?? [];

        /** @var string $statusIdToCharge */
        $statusIdToCharge = $request->getPayload()->get('statusIdToCharge', '');

        /** @var array $restrictedCountries */
        $restrictedCountries =  $request->toArray()['restrictedCountries'] ?? [];

        /** @var bool $sendBasketData */
        $sendBasketData = $request->getPayload()->get('sendBasketData', false);

        return new SavePaymentMethodConfigRequest(
            $type,
            $bookingMethod,
            $name,
            $description,
            $statusIdToCharge,
            $minAmount,
            $maxAmount,
            $surcharge,
            $restrictedCountries,
            $sendBasketData,
        );
    }

}
