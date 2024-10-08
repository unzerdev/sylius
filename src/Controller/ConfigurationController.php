<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ConfigurationController extends AbstractController
{
    public function configAction(): Response
    {
        return $this->render('@SyliusUnzerPlugin/config.html.twig');
    }
}
