<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;

/**
 * Class StoreController
 *
 * @package SyliusUnzerPlugin\Controller\Admin
 */
final class StoreController extends AbstractController
{
    /**
     * @return Response*
     */
    public function getOrderStatusesAction(): Response
    {
        $statuses  = AdminAPI::get()->stores()->getStoreOrderStatuses();

        return $this->json($statuses->toArray());
    }
}
