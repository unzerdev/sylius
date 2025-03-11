<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Controller\Admin;

use SplFileInfo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Unzer\Core\BusinessLogic\AdminAPI\AdminAPI;
use Unzer\Core\BusinessLogic\AdminAPI\PaymentPageSettings\Request\PaymentPageSettingsRequest;
use Unzer\Core\BusinessLogic\Domain\Connection\Exceptions\ConnectionSettingsNotFoundException;
use Unzer\Core\BusinessLogic\Domain\PaymentPageSettings\Exceptions\InvalidImageUrlException;
use Unzer\Core\BusinessLogic\Domain\Translations\Exceptions\InvalidTranslatableArrayException;
use Unzer\Core\BusinessLogic\Domain\Translations\Model\TranslationCollection;
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
     * @throws InvalidImageUrlException
     * @throws InvalidTranslatableArrayException
     */
    public function saveDesignAction(Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId');

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
        /** @var string $storeId */
        $storeId = $request->get('storeId');

        $response = AdminAPI::get()->paymentPageSettings($storeId)->getPaymentPageSettings();

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @throws ConnectionSettingsNotFoundException
     * @throws InvalidImageUrlException
     * @throws InvalidTranslatableArrayException
     * @throws UnzerApiException
     */
    public function createPreviewPageAction(Request $request): Response
    {
        /** @var string $storeId */
        $storeId = $request->get('storeId');
        $request = $this->createPaymentPageSettingRequest($request);
        $response = AdminAPI::get()->paymentPageSettings($storeId)->getPaymentPagePreview($request);

        return $this->json($response->toArray());
    }

    /**
     * @param Request $request
     *
     * @return PaymentPageSettingsRequest
     * @throws InvalidTranslatableArrayException
     */
    private function createPaymentPageSettingRequest(Request $request): PaymentPageSettingsRequest
    {
        $data = $request->request->all();
        $logoFile = $this->getFile($request, 'logoFile');

        $backgroundFile = $this->getFile($request, 'backgroundFile');

        $shopNameJson = $request->get('name');

        $shopName = TranslationCollection::fromArray($this->formatTranslatableField(
            (array)json_decode(is_string($shopNameJson) ? $shopNameJson : '[]', true)
        ));


        $logoImageUrl = $this->parseNullableField($data['logoImageUrl'] ?? null);
        $backgroundImageUrl =  $this->parseNullableField($data['backgroundImageUrl'] ?? null);
        $headerColor = $this->parseNullableField($data['headerColor'] ?? null);
        $brandColor = $this->parseNullableField($data['brandColor'] ?? null);
        $textColor = $this->parseNullableField($data['textColor'] ?? null);
        $linkColor = $this->parseNullableField($data['linkColor'] ?? null);
        $backgroundColor = $this->parseNullableField($data['backgroundColor'] ?? null);
        $footerColor = $this->parseNullableField($data['footerColor'] ?? null);
        $font = $this->parseNullableField($data['font'] ?? null);
        $shadows = $this->parseBoolField($data['shadows'] ?? null);
        $hideUnzerLogo = $this->parseBoolField($data['hideUnzerLogo'] ?? null);
        $hideBasket = $this->parseBoolField($data['hideBasket'] ?? null);
        $cornerRadius = $this->parseNullableField($data['cornerRadius'] ?? null);

        return new PaymentPageSettingsRequest(
            $shopName,
            $logoImageUrl,
            $logoFile,
            $backgroundImageUrl,
            $backgroundFile,
            $headerColor,
            $brandColor,
            $textColor,
            $linkColor,
            $backgroundColor,
            $footerColor,
            $font,
            $shadows,
            $hideUnzerLogo,
            $hideBasket,
            $cornerRadius
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

    /**
     * @param Request $request
     * @param string $name
     *
     * @return SplFileInfo|null
     */
    private function getFile(Request $request, string $name): ?SplFileInfo
    {
        $file = $request->files->get($name);
        return $file instanceof UploadedFile ? new SplFileInfo($file->getRealPath()) : null;
    }

    /**
     * @param string|null $value
     *
     * @return string|null
     */
    private function parseNullableField(?string $value): ?string
    {
        return $value === 'null' ? null : $value;
    }

    /**
     * @param string|null $value
     *
     * @return bool|null
     */
    private function parseBoolField(?string $value): ?bool
    {
        return $value === 'null' ? null : $value === 'true';
    }
}
