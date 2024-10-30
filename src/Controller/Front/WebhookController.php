<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Front;

use SyliusUnzerPlugin\EventListener\Event\DisableListenerEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\CurrencyMismatchException;
use Unzer\Core\BusinessLogic\Domain\Checkout\Exceptions\InvalidCurrencyCode;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\TransactionHistory\Exceptions\TransactionHistoryNotFoundException;
use Unzer\Core\BusinessLogic\WebhookAPI\Handler\Request\WebhookHandleRequest;
use Unzer\Core\BusinessLogic\WebhookAPI\WebhookAPI;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class WebhookController.
 *
 * @package SyliusUnzerPlugin\Controller\Front
 */
class WebhookController extends AbstractController
{
    /**
     * @param Request $request
     * @param EventDispatcherInterface $eventDispatcher
     * @return Response
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws CurrencyMismatchException
     * @throws InvalidCurrencyCode
     * @throws TransactionHistoryNotFoundException
     * @throws UnzerApiException
     */
    public function webhookAction(Request $request, EventDispatcherInterface $eventDispatcher): Response
    {
        $eventDispatcher->dispatch(new DisableListenerEvent(), DisableListenerEvent::WEBHOOKS);

        $storeId = $request->get('storeId');
        $storeId = is_string($storeId) ? $storeId : '';
        $response = WebhookAPI::get()->webhookHandle($storeId)
            ->handle(
                new WebhookHandleRequest(
                $request->getContent())
            );

        if (!$response->isSuccessful()) {
            return $this->json($response->toArray(), 400);
        }

        return $this->json([]);
    }
}
