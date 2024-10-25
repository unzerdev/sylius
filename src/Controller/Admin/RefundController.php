<?php


declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Payum\Core\Model\GatewayConfigInterface;
use Payum\Core\Payum;
use Payum\Core\Request\Refund as RefundAction;
use Payum\Core\Security\TokenInterface;
use SM\Factory\FactoryInterface;
use SM\SMException;
use Sylius\Component\Core\Model\PaymentInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Sylius\Component\Core\Repository\PaymentRepositoryInterface;
use Sylius\Component\Payment\PaymentTransitions;
use Sylius\Component\Resource\Exception\UpdateHandlingException;
use SyliusUnzerPlugin\Util\StaticHelper;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RefundController
{
    /** @var PaymentRepositoryInterface */
    private PaymentRepositoryInterface $paymentRepository;

    /** @var Payum */
    private Payum $payum;

    /** @var RequestStack */
    private RequestStack $requestStack;

    /** @var FactoryInterface */
    private FactoryInterface $stateMachineFactory;

    /** @var EntityManagerInterface */
    private EntityManagerInterface $paymentEntityManager;


    public function __construct(
        PaymentRepositoryInterface $paymentRepository,
        Payum $payum,
        RequestStack $requestStack,
        FactoryInterface $stateMachineFactory,
        EntityManagerInterface $paymentEntityManager,
    ) {
        $this->paymentRepository = $paymentRepository;
        $this->payum = $payum;
        $this->requestStack = $requestStack;
        $this->stateMachineFactory = $stateMachineFactory;
        $this->paymentEntityManager = $paymentEntityManager;
    }

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

        if ($factoryName === StaticHelper::UNZER_PAYMENT_METHOD_GATEWAY) {
            $this->applyStateMachineTransition($payment);
            $this->requestStack->getSession()->getFlashBag()->add('success', 'sylius.payment.refunded');
            return $this->redirectToReferer($request);
        }
        if (
            (!isset($payment->getDetails()['payment_mollie_id']) || !isset($payment->getDetails()['metadata']['refund_token'])) &&
            !isset($payment->getDetails()['order_mollie_id'])
        ) {
            $this->applyStateMachineTransition($payment);

            $this->requestStack->getSession()->getFlashBag()->add('info', 'sylius_mollie_plugin.ui.refunded_only_locally');

            return $this->redirectToReferer($request);
        }

        $hash = $payment->getDetails()['metadata']['refund_token'];

        /** @var TokenInterface|null $token */
        $token = $this->payum->getTokenStorage()->find($hash);

        if (!$token instanceof TokenInterface) {
            throw new BadRequestHttpException(sprintf('A token with hash `%s` could not be found.', $hash));
        }

        $gateway = $this->payum->getGateway($token->getGatewayName());

        try {
            if (isset($payment->getDetails()['order_mollie_id'])) {
                $gateway->execute(new RefundOrder($token));
            } else {
                $gateway->execute(new RefundAction($token));
            }

            $this->applyStateMachineTransition($payment);

            $this->requestStack->getSession()->getFlashBag()->add('success', 'sylius.payment.refunded');
        } catch (UpdateHandlingException $e) {
            $this->loggerAction->addNegativeLog(sprintf('Error with refund: %s', $e->getMessage()));
            $this->requestStack->getSession()->getFlashBag()->add('error', $e->getMessage());
        }

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
