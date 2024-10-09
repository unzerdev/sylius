<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Twig\Extension;

use SyliusMolliePlugin\Checker\ApplePay\ApplePayEnabledCheckerInterface;
use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodChecker;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UnzerEnabled extends AbstractExtension
{
    /**
     * @var UnzerPaymentMethodChecker $paymentMethodChecker
     */
    private UnzerPaymentMethodChecker $paymentMethodChecker;

    /**
     * @param UnzerPaymentMethodChecker $paymentMethodChecker
     */
    public function __construct(UnzerPaymentMethodChecker $paymentMethodChecker)
    {
        $this->paymentMethodChecker = $paymentMethodChecker;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'is_unzer_enabled',
                [$this->paymentMethodChecker, 'exists'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
