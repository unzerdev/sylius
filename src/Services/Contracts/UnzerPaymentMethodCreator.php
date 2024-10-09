<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Contracts;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Resource\Model\ResourceInterface;
/**
 * Interface UnzerPaymentMethodCreator
 */
interface UnzerPaymentMethodCreator
{

    /**
     * Create Unzer payment method if not exists
     */
    public function createIfNotExists(): void;
}
