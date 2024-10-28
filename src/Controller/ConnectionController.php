<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use SyliusUnzerPlugin\Util\StaticHelper;
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

/**
 * Class ConnectionController.
 *
 * @package SyliusUnzerPlugin\Controller
 */
final class ConnectionController extends AbstractController
{

    /**
     * @param Request $request
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     * @return Response
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidKeypairException
     * @throws InvalidModeException
     * @throws PrivateKeyInvalidException
     * @throws PublicKeyInvalidException
     * @throws UnzerApiException
     */
    public function connectAction(
        Request $request,
        ChannelRepositoryInterface $channelRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository
    ): Response {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');
        /** @var string $environment */
        $environment = $request->get('environment', '');
        /** @var string $publicKey */
        $publicKey = $request->get('publicKey', '');
        /** @var string $privateKey */
        $privateKey = $request->get('privateKey', '');
        $response = AdminAPI::get()->connection(
            $storeId
        )->connect(
            new ConnectionRequest(
                $environment,
                $publicKey,
                $privateKey
            )
        );

        $addChannelSuccess = true;

        if ($response->isSuccessful()) {
           $addChannelSuccess = $this->addChannel($channelRepository, $paymentMethodRepository, $storeId);
        }

        if(!$addChannelSuccess) {
            return $this->json(['error' => 'Payment method or channel not found'], 404);
        }

        return $this->json($response->toArray(), $response->isSuccessful() ? 200 : 400);
    }

    /**
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     * @param string $storeId
     *
     * @return bool
     */
    private function addChannel(
        ChannelRepositoryInterface $channelRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository, string $storeId
    ) : bool
    {
        /**@var ?ChannelInterface $channel */
        $channel = $channelRepository->find((int)$storeId);

        /** @var ?PaymentMethodInterface $paymentMethod */
        $paymentMethod = $paymentMethodRepository->findOneBy(['code' => StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY]
        );
        if (($paymentMethod === null) || !$channel instanceof ChannelInterface) {
            return false;
        }

        if (!$paymentMethod->hasChannel($channel)) {
            $paymentMethod->addChannel($channel);
            $paymentMethodRepository->add($paymentMethod);;
        }
        return true;
    }
}
