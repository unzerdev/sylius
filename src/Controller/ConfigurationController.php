<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodChecker;
use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\VarDumper;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\State\Request\StateRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\InvalidModeException;

final class ConfigurationController extends AbstractController
{

    /**
     * @var UnzerPaymentMethodCreator
     */
    private UnzerPaymentMethodCreator $paymentMethodCreator;

    /**
     * @var UnzerPaymentMethodChecker
     */
    private UnzerPaymentMethodChecker $unzerPaymentMethodChecker;

    /**
     * @param UnzerPaymentMethodCreator $paymentMethodCreator
     * @param UnzerPaymentMethodChecker $unzerPaymentMethodChecker
     */
    public function __construct(
        UnzerPaymentMethodCreator $paymentMethodCreator,
        UnzerPaymentMethodChecker $unzerPaymentMethodChecker
    )
    {
        $this->paymentMethodCreator = $paymentMethodCreator;
        $this->unzerPaymentMethodChecker = $unzerPaymentMethodChecker;
    }

    /**
     *
     * @return Response
     * @throws InvalidModeException
     */
    public function configAction(): Response
    {
        if (!$this->unzerPaymentMethodChecker->exists()) {
            return $this->redirectToRoute('sylius_admin_payment_method_index');
        }
        $stores = AdminAPI::get()->stores('')->getStores();
        $store = AdminAPI::get()->stores('')->getCurrentStore();

        return $this->render('@SyliusUnzerPlugin/config.html.twig', ['stores' => $stores->toArray(), 'store' => $store->toArray()]);
    }

    /**
     * @return Response
     */
    public function enableAction(): Response
    {
        $this->paymentMethodCreator->createIfNotExists();
        return $this->redirectToRoute('unzer_admin_config');
    }
}
