<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services;

use Sylius\Component\Core\Factory\PaymentMethodFactoryInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodCreator;
use SyliusUnzerPlugin\Util\StaticHelper;

/**
 * Class LoggerService.
 *
 * @package SyliusUnzerPlugin\Services
 */
class PaymentMethodCreator implements UnzerPaymentMethodCreator
{

    /**
     * @var PaymentMethodFactoryInterface<PaymentMethodInterface>
     */
    private PaymentMethodFactoryInterface $paymentMethodFactory;

    /**
     * @var PaymentMethodRepositoryInterface<PaymentMethodInterface>
     */
    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    /**
     * @param PaymentMethodFactoryInterface<PaymentMethodInterface> $paymentMethodFactory
     * @param PaymentMethodRepositoryInterface<PaymentMethodInterface> $paymentMethodRepository
     */
    public function __construct(
        PaymentMethodFactoryInterface $paymentMethodFactory,
        PaymentMethodRepositoryInterface $paymentMethodRepository)
    {
        $this->paymentMethodFactory = $paymentMethodFactory;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    /** @inheritdoc  */
    public function createIfNotExists(): void
    {
        $paymentMethod = $this->paymentMethodRepository->findOneBy(['code' => StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY]);
        if ($paymentMethod == null) {
            // add payment method in database.
            $paymentMethod = $this->paymentMethodFactory->createWithGateway(StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY);
            $gatewayConfig = $paymentMethod->getGatewayConfig();
            $gatewayConfig?->setGatewayName(StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY);
            $gatewayConfig?->setConfig(array_merge($gatewayConfig->getConfig(), ['use_authorize' => true]));
            $paymentMethod->setCode(StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY);
            $paymentMethod->setName(StaticHelper::UNZER_PAYMENT_METHOD_NAME);
            $this->paymentMethodRepository->add($paymentMethod);
        }
    }

}
