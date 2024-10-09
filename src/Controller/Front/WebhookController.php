<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Front;

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
    /**
     * @param string|null $name
     *
     * @return Response
     */
    public function webhookAction(Request $request): Response
    {

        return $this->json(['']);
    }
}
