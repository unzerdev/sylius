<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller;

use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request\PaymentPageSettingsRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use UnzerSDK\Exceptions\UnzerApiException;

/**
 * Class DesignController
 *
 * @package SyliusUnzerPlugin\Controller
 */
class DesignController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws InvalidTranslatableArrayException
     */
    public function saveDesignAction(Request $request): Response
    {
        $storeId = $request->get('storeId');
        $storeId = is_string($storeId) ? $storeId : '';

        $request = $this->createPaymentPageSettingRequest($request);

        $response = AdminAPI::get()->paymentPageSettings($storeId)->savePaymentPageSettings($request);

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function getDesignAction(Request $request): Response
    {
        $storeId = $request->get('storeId');
        $storeId = is_string($storeId) ? $storeId : '';

        $response = AdminAPI::get()->paymentPageSettings($storeId)->getPaymentPageSettings();

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws InvalidTranslatableArrayException
     * @throws UnzerApiException
     * @throws ConnectionSettingsNotFoundException
     */
    public function createPreviewPageAction(Request $request): Response
    {
        $storeId = $request->get('storeId');
        $storeId = is_string($storeId) ? $storeId : '';

        $request = $this->createPaymentPageSettingRequest($request);

        $response = AdminAPI::get()->paymentPageSettings($storeId)->getPaymentPagePreview($request);


        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return PaymentPageSettingsRequest
     */
    private function createPaymentPageSettingRequest(Request $request): PaymentPageSettingsRequest
    {
        $data = $request->request->all();
        $file = $request->files->get('logoFile');

        $fileLogo = null;

        if ($file instanceof UploadedFile) {
            $fileLogo = new SplFileInfo($file->getRealPath());
        }

        $shopNameJson = $request->get('name');
        $shopTaglineJson = $request->get('tagline');

        $shopName = $this->formatTranslatableField(
            (array)json_decode(is_string($shopNameJson) ? $shopNameJson : '[]', true)
        );

        $shopTagline = $this->formatTranslatableField(
            (array)json_decode(is_string($shopTaglineJson) ? $shopTaglineJson : '[]', true)
        );

        $logoImageUrl = ($data['logoImageUrl'] === 'null') ? null : $data['logoImageUrl'];
        $headerBackgroundColor = ($data['headerColor'] === 'null') ? null : $data['headerColor'];
        $headerFontColor = ($data['headerFontColor'] === 'null') ? null : $data['headerFontColor'];
        $shopNameBackgroundColor = ($data['shopNameBackground'] === 'null') ? null : $data['shopNameBackground'];
        $shopNameFontColor = ($data['shopNameColor'] === 'null') ? null : $data['shopNameColor'];
        $shopTaglineBackgroundColor = ($data['shopTaglineBackgroundColor'] === 'null') ? null : $data['shopTaglineBackgroundColor'];
        $shopTaglineFontColor = ($data['shopTaglineColor'] === 'null') ? null : $data['shopTaglineColor'];

        return new PaymentPageSettingsRequest(
            $shopName,
            $shopTagline,
            $logoImageUrl,
            $fileLogo,
            $headerBackgroundColor,
            $headerFontColor,
            $shopNameBackgroundColor,
            $shopNameFontColor,
            $shopTaglineBackgroundColor,
            $shopTaglineFontColor
        );
    }

    /**
     * Form translatable fields
     *
     * @param array $array
     * @return array
     */
    private function formatTranslatableField(array $array): array
    {
        $result = [];
        foreach ($array as $item) {
            $result[] = [
                'locale' => $item[0],
                'value' => $item[1]
            ];
        }

        return $result;
    }

}
