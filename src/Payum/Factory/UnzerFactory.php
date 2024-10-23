<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Payum\Factory;

use Payum\Core\GatewayFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use SyliusUnzerPlugin\Payum\Action\StatusAction;

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
            'use_authorize' => true,
            'payum.factory_name' => 'unzer_payment',
            'payum.factory_title' => 'Unzer Payment',
        ]);

        parent::populateConfig($config);
    }
}
