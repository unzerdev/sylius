<?php

namespace SyliusUnzerPlugin\Services\Integration;

use enshrined\svgSanitize\Sanitizer;
use Exception;
use Gaufrette\Filesystem;
use ReflectionException;
use SplFileInfo;
use Sylius\Component\Core\Model\ImageInterface;
use Sylius\Component\Core\Uploader\ImageUploader;
use SyliusUnzerPlugin\Models\LogoImage;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;
use Unzer\Core\BusinessLogic\Domain\Integration\Uploader\UploaderService as CoreImageService;

/**
 * Class ImageHandlerService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class ImageHandlerService implements CoreImageService
{

    private const MIME_SVG_XML = 'image/svg+xml';

    private const MIME_SVG = 'image/svg';

    private const PATH = 'unzer/image/logo.jpg';

    /**
     * @var ImageUploader
     */
    private ImageUploader $imageUploader;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var Sanitizer
     */
    private Sanitizer $sanitizer;

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @param ImageUploader $imageUploader
     * @param Filesystem $filesystem
     * @param RequestStack $requestStack
     */

    public function __construct(ImageUploader $imageUploader,
    Filesystem $filesystem, RequestStack $requestStack)
    {
        $this->imageUploader = $imageUploader;
        $this->filesystem = $filesystem;
        $this->requestStack = $requestStack;


        $this->sanitizer = new Sanitizer();
    }

    /**
     * @param SplFileInfo $file
     *
     * @return string
     * @throws ReflectionException
     */
    public function uploadImage(SplFileInfo $file): string
    {
        $symfonyFile = new File($file->getRealPath());

        $logoImage = $this->createLogoImage($symfonyFile);

        $this->upload($logoImage);

        return $this->getImageUrl($logoImage);
    }

    /**
     * @param File $file
     *
     * @return LogoImage
     */
    private function createLogoImage(File $file): LogoImage
    {

        $logoImage = new LogoImage();
        $logoImage->setFile($file);
        $logoImage->setType('logo');

        $logoImage->setPath(self::PATH);


        return $logoImage;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    public function removeImage(string $path): bool
    {
        try {
            $this->imageUploader->remove($path);
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * @param ImageInterface $image
     *
     * @return void
     */

    public function upload(ImageInterface $image): void
    {
        if (!$image->hasFile()) {
            return;
        }

        /** @var File $file */
        $file = $image->getFile();

        $fileContent = $this->sanitizeContent(file_get_contents($file->getPathname()), $file->getMimeType());

        $this->filesystem->write($image->getPath(), $fileContent, true);
    }

    /**
     * @param string $fileContent
     * @param string $mimeType
     *
     * @return string
     */
    protected function sanitizeContent(string $fileContent, string $mimeType): string
    {
        if (self::MIME_SVG_XML === $mimeType || self::MIME_SVG === $mimeType) {
            $fileContent = $this->sanitizer->sanitize($fileContent);
        }

        return $fileContent;
    }

    /**
     * @return string
     */
    private function getDomain(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request) {
            $scheme = $request->getScheme();
            $host = $request->getHost();

            return $scheme . '://' . $host;
        }

        return '';
    }

    /**
     * Retrieves the full URL for the uploaded logo image.
     *
     * @param LogoImage $logoImage
     * @return string
     * @throws ReflectionException
     */
    private function getImageUrl(LogoImage $logoImage): string
    {
        $adapter = $this->filesystem->getAdapter();
        $reflection = new \ReflectionClass($adapter);
        $directory = $reflection->getProperty('directory');
        $path = $directory->getValue($adapter);

        $parts = explode('/public', $path);
        if (count($parts) > 1) {
            $path = ltrim($parts[1], '/');
        }

        $domain = $this->getDomain();
        return $domain . '/' . $path . '/' . $logoImage->getPath();
    }
}
