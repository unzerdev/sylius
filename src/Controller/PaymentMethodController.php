<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Sylius\Bundle\CoreBundle\Controller\PaymentMethodController as BasePaymentMethodController;
use Sylius\Component\Resource\ResourceActions;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentMethodController extends BasePaymentMethodController
{
    public function updateAction(Request $request): Response
    {
        $configuration = $this->requestConfigurationFactory->create($this->metadata, $request);
        $this->isGrantedOr403($configuration, ResourceActions::SHOW);
        $product = $this->findOr404($configuration);

        if (true) {
            return $this->redirectToRoute('unzer_test');
        }

        // some custom provider service to retrieve recommended products
        $recommendationService = $this->get('app.provider.product');

        $recommendedProducts = $recommendationService->getRecommendedProducts($product);

        $this->eventDispatcher->dispatch(ResourceActions::SHOW, $configuration, $product);

        if ($configuration->isHtmlRequest()) {
            return $this->render($configuration->getTemplate(ResourceActions::SHOW . '.html'), [
                'configuration' => $configuration,
                'metadata' => $this->metadata,
                'resource' => $product,
                'recommendedProducts' => $recommendedProducts,
                $this->metadata->getName() => $product,
            ]);
        }

        return $this->createRestView($configuration, $product);
    }
}
