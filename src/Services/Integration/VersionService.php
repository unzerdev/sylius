<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Composer\InstalledVersions;
use Unzer\Core\BusinessLogic\Domain\Version\Models\Version;
use Unzer\Core\BusinessLogic\Domain\Integration\Versions\VersionService as VersionServiceInterface;

/**
 * Class VersionService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class VersionService implements VersionServiceInterface
{
    /**
     * @return Version
     */
    public function getVersion(): Version
    {
        $version = InstalledVersions::getPrettyVersion('unzer/sylius-plugin');

        return new Version($version);
    }
}
