<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\RefundPlugin\Exception\OrderNotAvailableForRefunding;
use SyliusUnzerPlugin\Refund\PaymentRefundInterface;
use SyliusUnzerPlugin\Util\StaticHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RefundController
{
    /** @var PaymentRepositoryInterface<PaymentInterface> */
    private PaymentRepositoryInterface $paymentRepository;

    /** @var Payum */
    private Payum $payum;

    /** @var RequestStack */
    private RequestStack $requestStack;

    /** @var FactoryInterface */
    private FactoryInterface $stateMachineFactory;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $paymentEntityManager;

    /** @var PaymentRefundInterface */
    private  PaymentRefundInterface $paymentRefund;


    /**
     * @param PaymentRepositoryInterface<PaymentInterface> $paymentRepository
     * @param Payum $payum
     * @param RequestStack $requestStack
     * @param FactoryInterface $stateMachineFactory
     * @param EntityManagerInterface $paymentEntityManager
     * @param PaymentRefundInterface $paymentRefund

     */
    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        Payum $payum,
        RequestStack $requestStack,
        FactoryInterface $stateMachineFactory,
        EntityManagerInterface $paymentEntityManager,
        PaymentRefundInterface $paymentRefund
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->payum = $payum;
        $this->requestStack = $requestStack;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->paymentEntityManager = $paymentEntityManager;
        $this->paymentRefund = $paymentRefund;
    }

    /**
     * @throws SMException
     */
    public function __invoke(Request $request): Response
    {
        /** @var PaymentInterface|null $payment */
        $payment = $this->paymentRepository->find($request->get('id'));

        if (null === $payment) {
            throw new NotFoundHttpException();
        }

        /** @var PaymentMethodInterface $paymentMethod */
        $paymentMethod = $payment->getMethod();

        /** @var GatewayConfigInterface $gatewayConfig */
        $gatewayConfig = $paymentMethod->getGatewayConfig();
        $factoryName = $gatewayConfig->getGatewayName();
        /** @var Session $session */
        $session = $this->requestStack->getSession();
        if ($factoryName !== StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY) {
            $this->applyStateMachineTransition($payment);
            $session->getFlashBag()->add('success', 'sylius.payment.refunded');
            return $this->redirectToReferer($request);
        }

        $order = $payment->getOrder();

        if ($order === null) {
            throw new OrderNotAvailableForRefunding();
        }
        try {
            $this->paymentRefund->refund(
                (string)$order->getId(),
                $order->getTotal()
            );
        } catch (\Exception $e) {
            $session->getFlashBag()->add('error', $e->getMessage());

            return $this->redirectToReferer($request);
        }
        $session->getFlashBag()->add('success', 'sylius.payment.refunded');

        return $this->redirectToReferer($request);


    }

    /**
     * @throws SMException
     */
    private function applyStateMachineTransition(PaymentInterface $payment): void
    {
        $stateMachine = $this->stateMachineFactory->get($payment, PaymentTransitions::GRAPH);

        if (!$stateMachine->can(PaymentTransitions::TRANSITION_REFUND)) {
            throw new BadRequestHttpException();
        }

        $stateMachine->apply(PaymentTransitions::TRANSITION_REFUND);

        $this->paymentEntityManager->flush();
    }

    private function redirectToReferer(Request $request): Response
    {
        /** @var string $url */
        $url = $request->headers->get('referer');

        return new RedirectResponse($url);
    }
}
