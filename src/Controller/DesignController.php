<?php

namespace SyliusUnzerPlugin\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DesignController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function saveDesignAction(Request $request): Response
    {
        return $this->json([]);
    }
}
