<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Contracts;

/**
 * Interface UnzerPaymentMethodCreator
 */
interface UnzerPaymentMethodChecker
{

    /**
     * Check if Unzer payment method exists
     * @return bool
     */
    public function exists(): bool;
}
