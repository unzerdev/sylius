<?php

namespace SyliusUnzerPlugin\Controller;

use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request\PaymentPageSettingsRequest;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;

class DesignController extends AbstractController
{
    /**
     * @param Request $request
     *
     * @return Response
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

        $shopName = $this->formatTranslatableField(json_decode($request->get('name')));
        $shopTagline = $this->formatTranslatableField(json_decode($request->get('tagline')));

        $logoImageUrl = $data['logoImageUrl'] ?? '';
        $headerBackgroundColor = $data['headerColor'] ?? '';
        $headerFontColor = $data['headerFontColor'] ?? '';
        $shopNameBackgroundColor = $data['shopNameBackground'] ?? '';
        $shopNameFontColor = $data['shopNameColor'] ?? '';
        $shopTaglineBackgroundColor = $data['shopTaglineBackgroundColor'] ?? '';
        $shopTaglineFontColor = $data['shopTaglineColor'] ?? '';

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
