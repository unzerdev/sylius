<?php

namespace SyliusUnzerPlugin\DependencyInjection\Compiler;

use SyliusUnzerPlugin\Bootstrap\Bootstrap;
use SyliusUnzerPlugin\Services\LoggerService;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Unzer\Core\Infrastructure\Logger\Interfaces\ShopLoggerAdapter;
use Unzer\Core\Infrastructure\ServiceRegister;

/**
 * Class UnzerCompilerPass.
 *
 * @package SyliusUnzerPlugin\DependencyInjection\Compiler
 */
class UnzerCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     *
     * @return void
     * @throws \Exception
     */
    public function process(ContainerBuilder $container)
    {

        $bootstrapDefinition = new Definition(
            Bootstrap::class,
            [
                $container->getDefinition(ShopLoggerAdapter::class)
            ]
        );
        $bootstrapDefinition->setPublic(true);

        $container->setDefinition(Bootstrap::class, $bootstrapDefinition);

    }
}
