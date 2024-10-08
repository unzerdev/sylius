<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use SyliusUnzerPlugin\Bootstrap\Bootstrap;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class SyliusUnzerPlugin.
 *
 * @package SyliusUnzerPlugin
 */
final class SyliusUnzerPlugin extends Bundle
{
    use SyliusPluginTrait;

    /**
     * @return string
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function boot(): void
    {
        parent::boot();
        /** @var Bootstrap $bootstrap */
        $bootstrap = $this->container->get(Bootstrap::class);
        $bootstrap::init();
    }
}
