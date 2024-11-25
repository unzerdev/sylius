<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Unzer\Core\Infrastructure\Configuration\Configuration;
use Unzer\Core\Infrastructure\Singleton;

/**
 * Class ConfigurationService.
 *
 * @package SyliusUnzerPlugin\Services
 */
class ConfigurationService extends Configuration
{
    /**
     * @var UrlGeneratorInterface
     */
    private  UrlGeneratorInterface $urlGenerator;

    /**
     * Singleton instance of this class.
     *
     * @var ?Singleton
     */
    protected static ?Singleton $instance = null;

    /**
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        parent::__construct();

        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @inheritDoc
     */
    public function getIntegrationName(): string
    {
        return 'SyliusUnzer';
    }

    /**
     * @inheritDoc
     */
    public function getAsyncProcessUrl(string $guid): string
    {
        return $this->urlGenerator->generate(
            'unzer_async',
            ['guid' => $guid],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }
}
