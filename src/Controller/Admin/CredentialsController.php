<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sylius\Component\Channel\Repository\ChannelRepositoryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use SyliusUnzerPlugin\Util\StaticHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ReconnectRequest;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\ReRegisterWebhookRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionDataNotFound;
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
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return Response
     *
     * @throws UnzerApiException
     */
    public function disconnectAction(
        Request $request,
        ChannelRepositoryInterface $channelRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        $response = AdminAPI::get()->disconnect($storeId)->disconnect();

        if ($response->isSuccessful()) {
            $this->removePaymentMethodFromChannel(
                $storeId,
                $channelRepository,
                $paymentMethodRepository,
                $entityManager
            );
        }

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidModeException
     * @throws ConnectionDataNotFound
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

    /**
     * @param string $storeId
     * @param ChannelRepositoryInterface<ChannelInterface> $channelRepository
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return void
     */
    private function removePaymentMethodFromChannel(
        string $storeId,
        ChannelRepositoryInterface $channelRepository,
        PaymentMethodRepositoryInterface $paymentMethodRepository,
        EntityManagerInterface $entityManager,
    ): void {
        /**@var ?ChannelInterface $channel */
        $channel = $channelRepository->find((int)$storeId);
        /** @var ?PaymentMethodInterface $paymentMethod */
        $paymentMethod = $paymentMethodRepository->findOneBy(['code' => StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY]
        );

        if (($paymentMethod === null) || !$channel instanceof ChannelInterface) {
            return;
        }

        if ($paymentMethod->hasChannel($channel)) {
            $paymentMethod->removeChannel($channel);
            $entityManager->persist($paymentMethod);
            $entityManager->flush();
        }
    }
}
