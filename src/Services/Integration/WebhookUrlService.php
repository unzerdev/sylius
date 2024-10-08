<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;

/**
 * Class WebhookUrlService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class WebhookUrlService implements WebhookUrlServiceInterface
{
//TODO IMPLEMENT METHOD
    public function getWebhookUrl(): string
    {
      return 'https://api.sylius.com/webhooks/';
    }
}
