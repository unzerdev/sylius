<?php

namespace SyliusUnzerPlugin\Services\Integration;

use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;

/**
 * Class EncryptorService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class EncryptorService implements EncryptorInterface
{
//TODO IMPLEMENT METHODS
    public function encrypt(string $data): string
    {
        return $data;
    }

    public function decrypt(string $encryptedData): string
    {
        return $encryptedData;
    }
}
