<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class PaymentErrorController.
 *
 * @package SyliusUnzerPlugin\Controller\Front
 */
class PaymentErrorController extends AbstractController
{
    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function process(Request $request): Response
    {
        /** @var string $errorMessage */
        $errorMessage = $request->get('errorMessage' , '');

        /** @var FlashBagInterface $flashBag */
        $flashBag = $request->getSession()->getBag('flashes');
        $flashBag->add('error', $this->translator->trans('sylius_unzer_plugin.checkout.payment_processing_error') . $errorMessage );

        return new JsonResponse([], Response::HTTP_BAD_REQUEST);
    }
}
