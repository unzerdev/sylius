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
     */
    public function process(ContainerBuilder $container)
    {
        $ref = $container->findDefinition(ShopLoggerAdapter::class);

      //  Bootstrap::init();

        $bootstrapDefinition = new Definition(
            Bootstrap::class,
            [
                $container->getDefinition(ShopLoggerAdapter::class)
            ]
        );
        $bootstrapDefinition->setPublic(true);
        $bootstrapDefinition->setAutowired(true);



        Bootstrap::init();
    }
}
