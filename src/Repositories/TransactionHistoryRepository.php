<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Repositories;

use SyliusUnzerPlugin\Entity\TransactionHistoryEntity;

/**
 * Class TransactionHistoryRepository.
 *
 * @package SyliusUnzerPlugin\Repositories
 */
class TransactionHistoryRepository extends BaseRepository
{
    /**
     * Fully qualified name of this class.
     */
    public const THIS_CLASS_NAME = __CLASS__;

    /**
     * Name of the base entity table in database.
     */
    public const TABLE_NAME = 'unzer_transactions';

    /**
     * @var string
     */
    protected static string $doctrineModel = TransactionHistoryEntity::class;
}
