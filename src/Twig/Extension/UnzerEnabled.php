<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Twig\Extension;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UnzerEnabled extends AbstractExtension
{
    /**
     * @var PaymentMethodRepositoryInterface<PaymentMethodInterface>
     */
    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    /**
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     */
    public function __construct(PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'is_unzer_enabled',
                [$this, 'isEnabled'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => 'unzer_payment']);

        return $paymentMethod !== null;
    }
}
