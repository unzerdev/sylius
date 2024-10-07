<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use SyliusUnzerPlugin\Bootstrap\Bootstrap;
use SyliusUnzerPlugin\DependencyInjection\Compiler\UnzerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new UnzerCompilerPass());
    }
}
