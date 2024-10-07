<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Factory;

use Payum\Core\GatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;

/**
 * Class UnzerFactory.
 *
 * @package Acme\SyliusExamplePlugin\Factory
 */
class UnzerFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'unzer_payment',
            'payum.factory_title' => 'Unzer Payment',
        ]);

        parent::populateConfig($config);
    }
}
