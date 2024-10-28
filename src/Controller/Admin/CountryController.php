<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;

/**
 * Class CountryController
 *
 * @package SyliusUnzerPlugin\Controller\Admin
 */
final class CountryController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response*
     */
    public function getCountriesAction(Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId', '');

        $countries  = AdminAPI::get()->countries($storeId)->getCountries();

        return $this->json($countries->toArray());
    }
}
