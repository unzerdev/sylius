<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ConnectionRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidKeypairException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PrivateKeyInvalidException;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\PublicKeyInvalidException;
use UnzerSDK\Exceptions\UnzerApiException;

final class ConnectionController extends AbstractController
{

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws InvalidModeException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     */
    public function connectAction(Request $request): Response
    {

        $response = AdminAPI::get()->connection(
            $request->get('storeId')
        )->connect(
            new ConnectionRequest(
                $request->get('environment') ?? '',
                $request->get('publicKey') ?? '',
                $request->get('privateKey') ?? ''
            )
        );

        return $this->json($response->toArray(), $response->isSuccessful() ? 200 : 400);
    }
}
