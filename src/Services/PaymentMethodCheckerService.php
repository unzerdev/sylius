<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services;

use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodChecker;
use SyliusUnzerPlugin\Util\StaticHelper;

/**
 * Class LoggerService.
 *
 * @package SyliusUnzerPlugin\Services
 */
class PaymentMethodCheckerService implements UnzerPaymentMethodChecker
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

    /** @inheritdoc  */
    public function exists(): bool
    {
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY]);

        return $paymentMethod !== null;
    }
}
