<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors;

use Unzer\Core\BusinessLogic\Domain\Integration\PaymentPage\Processors\CustomerProcessor as CustomerProcessorInterface;
use Unzer\Core\BusinessLogic\Domain\PaymentPage\Models\PaymentPageCreateContext;
use UnzerSDK\Resources\Customer;

/**
 * Class CustomerProcessor
 *
 * @package SyliusUnzerPlugin\Services\Integration\PaymentPage\Processors
 */
class CustomerProcessor implements CustomerProcessorInterface
{

    public function process(Customer $customer, PaymentPageCreateContext $context): void
    {
        // TODO: Implement process() method.
    }
}
