<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;

final class CountryController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response*
     */
    public function getCountriesAction(Request $request): Response
    {
        $countries  = AdminAPI::get()->countries($request->get('storeId'))->getCountries();

        return $this->json($countries->toArray());
    }
}
