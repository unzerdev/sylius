<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Services\Integration;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\BadFormatException;
use Defuse\Crypto\Exception\EnvironmentIsBrokenException;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Unzer\Core\BusinessLogic\Domain\Integration\Utility\EncryptorInterface;

/**
 * Class EncryptorService.
 *
 * @package SyliusUnzerPlugin\Services\Integration
 */
class EncryptorService implements EncryptorInterface
{
    /**
     * @var Key
     */
    private Key $key;

    /**
     * @param string $encryptionKeyString
     *
     * @throws BadFormatException
     * @throws EnvironmentIsBrokenException
     */
    public function __construct(string $encryptionKeyString)
    {
        $this->key = Key::loadFromAsciiSafeString($encryptionKeyString);
    }

    /**
     * @param string $data
     *
     * @return string
     *
     * @throws EnvironmentIsBrokenException
     */
    public function encrypt(string $data): string
    {
        return Crypto::encrypt($data, $this->key);
    }

    /**
     * @param string $encryptedData
     *
     * @return string
     *
     * @throws EnvironmentIsBrokenException
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function decrypt(string $encryptedData): string
    {
        return Crypto::decrypt($encryptedData, $this->key);
    }
}
