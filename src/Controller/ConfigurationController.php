<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Exception;
use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodChecker;
use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\Connection\Request\GetCredentialsRequest;
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
    ) {
        $this->paymentMethodCreator = $paymentMethodCreator;
        $this->unzerPaymentMethodChecker = $unzerPaymentMethodChecker;
    }

    /**
     *
     * @param Request $request
     *
     * @return Response
     * @throws InvalidModeException
     * @throws Exception
     */
    public function configAction(Request $request): Response
    {
        $selectedStore = $request->query->get('store', false);

        if (!$this->unzerPaymentMethodChecker->exists()) {
            return $this->redirectToRoute('sylius_admin_payment_method_index');
        }
        $stores = AdminAPI::get()->stores()->getStores();
        $store = !$selectedStore ? AdminAPI::get()->stores()->getCurrentStore() : AdminAPI::get()->stores(
        )->getStoreById((int)$selectedStore);
        $version = AdminAPI::get()->version()->getVersion();
        $mode = $store->toArray()['mode'];
        $credentials = AdminAPI::get()->connection($store->toArray()['storeId'])->getCredentials(
            new GetCredentialsRequest($mode)
        )->toArray();
        $locales = AdminAPI::get()->languages($store->toArray()['storeId'])->getLanguages()->toArray();

        $connectionData = $credentials['connectionData'] ?? [];


        return $this->render(
            '@SyliusUnzerPlugin/config.html.twig',
            [
                'stores' => $stores->toArray(),
                'store' => $store->toArray(),
                'version' => $version->toArray(),
                'connectionData' => $connectionData,
                'locales' => $locales,
            ]
        );
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
