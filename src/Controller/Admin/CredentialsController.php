<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ReconnectRequest;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ReRegisterWebhookRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidKeypairException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PrivateKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PublicKeyInvalidException;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class CredentialsController
 *
 * @package SyliusUnzerPlugin\Controller
 */
class CredentialsController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws InvalidModeException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     * @throws UnzerApiException|QueryFilterInvalidParamException
     */
    public function reconnectAction(Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        /** @var string $environment */
        $environment = $request->get('environment', '');

        /** @var string $publicKey */
        $publicKey = $request->get('publicKey', '');

        /** @var string $privateKey */
        $privateKey = $request->get('privateKey', '');

        $deleteConfig = (bool)$request->get('deleteConfig', '');

        $response = AdminAPI::get()->connection(
            $storeId
        )->reconnect(
            new ReconnectRequest(
                $environment,
                $publicKey,
                $privateKey,
                $deleteConfig
            )
        );

        return $this->json($response->toArray(), $response->isSuccessful() ? 200 : 400);
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws UnzerApiException
     */
    public function disconnectAction(Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        $response = AdminAPI::get()->disconnect($storeId)->disconnect();

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidModeException
     * @throws UnzerApiException
     */
    public function reRegisterWebhookAction(Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        /** @var string $environment */
        $environment = $request->get('environment', '');

        $response = AdminAPI::get()->connection($storeId)->reRegisterWebhooks(new ReregisterWebhookRequest($environment));

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function getCredentialsData(Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        $response = AdminAPI::get()->connection($storeId)->getCredentials();

        return $this->json($response->toArray());
    }
}
