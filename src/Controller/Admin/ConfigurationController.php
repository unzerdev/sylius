<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Exception;
use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodChecker;
use SyliusUnzerPlugin\Services\Contracts\UnzerPaymentMethodCreator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\TaskRunnerWakeup;

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
     * @var TaskRunnerWakeup
     */
    private TaskRunnerWakeup $taskRunnerWakeup;

    /**
     * @param UnzerPaymentMethodCreator $paymentMethodCreator
     * @param UnzerPaymentMethodChecker $unzerPaymentMethodChecker
     * @param TaskRunnerWakeup $taskRunnerWakeup
     */
    public function __construct(
        UnzerPaymentMethodCreator $paymentMethodCreator,
        UnzerPaymentMethodChecker $unzerPaymentMethodChecker,
        TaskRunnerWakeup $taskRunnerWakeup
    ) {
        $this->paymentMethodCreator = $paymentMethodCreator;
        $this->unzerPaymentMethodChecker = $unzerPaymentMethodChecker;
        $this->taskRunnerWakeup = $taskRunnerWakeup;
    }

    /**
     * @param Request $request
     *
     * @return Response
     * @throws Exception
     */
    public function configAction(Request $request): Response
    {
        /**@var bool|int $selectedStore**/
        $selectedStore = $request->query->get('store', false);
        $this->taskRunnerWakeup->wakeup();
        if (!$this->unzerPaymentMethodChecker->exists()) {
            return $this->redirectToRoute('sylius_admin_payment_method_index');
        }
        $stores = AdminAPI::get()->stores()->getStores();
        $currentLocale = $request->getLocale();

        $store = $selectedStore === false ? AdminAPI::get()->stores()->getCurrentStore() : AdminAPI::get()->stores(
        )->getStoreById((int)$selectedStore);

        $version = AdminAPI::get()->version()->getVersion();
        $locales = AdminAPI::get()->languages($store->toArray()['storeId'])->getLanguages()->toArray();


        return $this->render(
            '@SyliusUnzerPlugin/config.html.twig',
            [
                'stores' => $stores->toArray(),
                'store' => $store->toArray(),
                'version' => $version->toArray(),
                'locales' => $locales,
                'currentLocale' => $currentLocale,
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
