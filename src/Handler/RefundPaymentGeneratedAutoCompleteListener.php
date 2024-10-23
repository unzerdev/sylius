<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Handler;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentMethodRepositoryInterface;
use Sylius\RefundPlugin\Entity\RefundPaymentInterface;
use Sylius\RefundPlugin\Event\RefundPaymentGenerated;
use Sylius\RefundPlugin\StateResolver\RefundPaymentCompletedStateApplierInterface;
use SyliusUnzerPlugin\Util\StaticHelper;
use Webmozart\Assert\Assert;

final class RefundPaymentGeneratedAutoCompleteListener
{
    /**
     * @var PaymentMethodRepositoryInterface<PaymentMethodInterface>
     */
    private PaymentMethodRepositoryInterface $paymentMethodRepository;

    /** @var RefundPaymentCompletedStateApplierInterface */
    private RefundPaymentCompletedStateApplierInterface $refundPaymentCompletedStateApplier;

    /**
     * @var EntityRepository $refundPaymentRepository
     */
    private EntityRepository $refundPaymentRepository;

    public function __construct(
        EntityRepository $refundPaymentInterface,
        RefundPaymentCompletedStateApplierInterface $refundPaymentCompletedStateApplier,
        PaymentMethodRepositoryInterface $paymentMethodRepository
    ) {
        $this->refundPaymentRepository = $refundPaymentInterface;
        $this->refundPaymentCompletedStateApplier = $refundPaymentCompletedStateApplier;
        $this->paymentMethodRepository = $paymentMethodRepository;
    }

    public function __invoke(RefundPaymentGenerated $refundPaymentGenerated): void
    {
        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $this->paymentMethodRepository->find($refundPaymentGenerated->paymentMethodId());

        Assert::notNull($paymentMethod->getGatewayConfig());
//        if (StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY !== $paymentMethod->getGatewayConfig()->getGatewayName()) {
//            return;
//        }

        /** @var RefundPaymentInterface $refundPayment */
        $refundPayment = $this->refundPaymentRepository->find($refundPaymentGenerated->id());

        $this->refundPaymentCompletedStateApplier->apply($refundPayment);
    }
}
