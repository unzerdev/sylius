<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Front;

use Payum\Core\Payum;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class WebhookController.
 *
 * @package SyliusUnzerPlugin\Controller\Front
 */
class WebhookController extends AbstractController
{

    /** @var Payum */
    private Payum $payum;

    /**
     * @param Payum $payum
     */
    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }


    /**
     * @param Request $request
     * @return Response
     */
    public function webhookAction(Request $request): Response
    {
        return $this->json(['']);
    }
}
