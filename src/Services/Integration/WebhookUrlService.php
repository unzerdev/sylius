<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Unzer\Core\BusinessLogic\Domain\Integration\Webhook\WebhookUrlServiceInterface;

/**
 * Class WebhookUrlService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class WebhookUrlService implements WebhookUrlServiceInterface
{
    /**
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return $this->urlGenerator->generate(
            'unzer_webhook',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
