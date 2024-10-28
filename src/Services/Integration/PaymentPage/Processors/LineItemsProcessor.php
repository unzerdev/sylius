<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\LineItemsProcessor as LineItemsProcessorInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Basket;

/**
 * Class LineItemsProcessor
 *
 * @package SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors
 */
class LineItemsProcessor implements LineItemsProcessorInterface
{

    public function process(Basket $basket, PaymentPageCreateContext $context): void
    {
        // TODO: Implement process() method.
    }
}
