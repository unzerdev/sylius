<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\Infrastructure\ServiceRegister;
use Unzer\Core\Infrastructure\TaskExecution\Interfaces\AsyncProcessService;

/**
 * Class AsyncProcessController.
 *
 * @package SyliusUnzerPlugin\Controller\Front
 */
class AsyncProcessController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     */
    public function asyncAction(Request $request): Response
    {
        /** @var string $guid */
        $guid = $request->get('guid');

        $this->getAsyncProcessService()->runProcess($guid);

        return $this->json(['success'  => true]);
    }

    /**
     * @return AsyncProcessService
     */
    private function getAsyncProcessService(): AsyncProcessService
    {
        /** @var AsyncProcessService $asyncProcessService */
        $asyncProcessService = ServiceRegister::getService(AsyncProcessService::CLASS_NAME);

        return $asyncProcessService;
    }
}
