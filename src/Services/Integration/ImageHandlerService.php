<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration;

use enshrined\svgSanitize\Sanitizer;
use Gaufrette\Filesystem;
use ReflectionException;
use RuntimeException;
use SplFileInfo;
use Sylius\Component\Core\Model\ImageInterface;
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

    private const PATH = 'unzer/image/';

    private const LOGO_NAME = 'logo.jpg';

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
     * @param Filesystem $filesystem
     * @param RequestStack $requestStack
     */

    public function __construct(Filesystem $filesystem, RequestStack $requestStack)
    {
        $this->filesystem = $filesystem;
        $this->requestStack = $requestStack;


        $this->sanitizer = new Sanitizer();
    }

    /**
     *
     * @param SplFileInfo $file
     * @param string|null $name
     * @return string
     * @throws ReflectionException
     */
    public function uploadImage(SplFileInfo $file, ?string $name = null): string
    {
        $symfonyFile = new File($file->getRealPath());

        $logoImage = $this->createLogoImage($symfonyFile, $name);

        $this->upload($logoImage);

        return $this->getImageUrl($logoImage);
    }

    /**
     * @param File $file
     * @param string|null $name
     *
     * @return LogoImage
     */
    private function createLogoImage(File $file, ?string $name = null): LogoImage
    {
        $imagePath = self::PATH . self::LOGO_NAME;

        if ($name !== null) {
            $imagePath = self::PATH . $name;
        }

        $logoImage = new LogoImage();
        $logoImage->setFile($file);
        $logoImage->setType('logo');

        $logoImage->setPath($imagePath);


        return $logoImage;
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

        $fileContent = $this->sanitizeContent((string)file_get_contents($file->getPathname()), (string)$file->getMimeType());

        /**@var string|null $path */
        $path = $image->getPath();

        if ($path === null) {
            throw new RuntimeException('Image path cannot be null.');
        }

        $this->filesystem->write($path, $fileContent, true);
    }

    /**
     * @param string $fileContent
     * @param string $mimeType
     *
     * @return string
     */
    protected function sanitizeContent(string $fileContent, string $mimeType): string
    {
        if (!(self::MIME_SVG_XML === $mimeType || self::MIME_SVG === $mimeType)) {
            return $fileContent;
        }

        $sanitizedContent = $this->sanitizer->sanitize($fileContent);

        if ($sanitizedContent === false) {
            throw new RuntimeException('Sanitization failed.');
        }

        return $sanitizedContent;
    }

    /**
     * @return string
     */
    private function getDomain(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request !== null) {
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

        /** @var string $path */
        $path = $directory->getValue($adapter);

        $parts = explode('/public', $path);
        if (count($parts) > 1) {
            $path = ltrim($parts[1], '/');
        }

        $domain = $this->getDomain();
        return $domain . '/' . $path . '/' . $logoImage->getPath();
    }
}
