<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\GetCredentialsRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use UnzerSDK\Exceptions\UnzerApiException;

class CredentialsController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws UnzerApiException
     */
    public function disconnectAction(Request $request): Response
    {
        $storeId = $this->getStoreIdString($request);

        $response = AdminAPI::get()->disconnect($storeId)->disconnect();

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws UnzerApiException
     */
    public function reRegisterWebhookAction(Request $request): Response
    {
        $storeId = $this->getStoreIdString($request);

        $response = AdminAPI::get()->connection($storeId)->reRegisterWebhooks();

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws InvalidModeException
     * @throws \Exception
     */
    public function getCredentialsData(Request $request): Response
    {
        $storeId = $this->getStoreIdString($request);
        $store = AdminAPI::get()->stores()->getStoreById((int)$storeId);
        $mode = $store->toArray()['mode'];

        $response = AdminAPI::get()->connection($storeId)->getCredentials(new GetCredentialsRequest($mode));

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function getStoreIdString(Request $request): string
    {
        $storeId = $request->get('storeId');
        return is_string($storeId) ? $storeId : '';
    }
}
