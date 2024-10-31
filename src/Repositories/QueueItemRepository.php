<?php

declare(strict_types=1);

namespace SyliusUnzerPlugin\Repositories;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use SyliusUnzerPlugin\Entity\QueueItemEntity;
use Unzer\Core\Infrastructure\Logger\Logger;
use Unzer\Core\Infrastructure\ORM\Configuration\Index;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use  Unzer\Core\Infrastructure\ORM\Interfaces\QueueItemRepository as BaseItemRepository;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Unzer\Core\Infrastructure\ORM\Utility\IndexHelper;
use Unzer\Core\Infrastructure\TaskExecution\Exceptions\QueueItemSaveException;
use Unzer\Core\Infrastructure\TaskExecution\QueueItem;

/**
 * Class QueueItemRepository.
 *
 * @package SyliusUnzerPlugin\Repositories
 */
class QueueItemRepository extends BaseRepository implements BaseItemRepository
{
    /**
     * Fully qualified name of this class.
     */
    public const THIS_CLASS_NAME = __CLASS__;

    /**
     * Name of the base entity table in database.
     */
    public const TABLE_NAME = 'unzer_queue';

    /**
     * @var string
     */
    protected static string $doctrineModel = QueueItemEntity::class;

    /**
     * @param int $priority
     * @param int $limit
     *
     * @return QueueItem[]
     */
    public function findOldestQueuedItems(int $priority, int $limit = 10): array
    {
        $result = [];

        try {
            /** @var EntityManagerInterface $entityManager */
            $entityManager = $this->getEntityManager();

            $connection = $entityManager->getConnection();
            $ids = $this->getQueueIdsForExecution($priority, $limit);
            if (count($ids) == 0) {
                return $result;
            }

            $query = $connection->createQueryBuilder()
                ->select('queue.id', 'queue.data')
                ->from(self::TABLE_NAME, 'queue')
                ->where('queue.id IN(:ids)')
                ->orderBy('queue.id');
            $query->setParameter('ids', $ids, ArrayParameterType::INTEGER);

            $rawItems = $query->executeQuery()->fetchAllAssociative();

            $result = $this->inflateQueueItems(count($rawItems) > 0 ? $rawItems : []);
        } catch (Exception $e) {
            Logger::logError($e->getMessage());
        }

        return $result;
    }

    /**
     * @param QueueItem $queueItem
     * @param array $additionalWhere
     *
     * @return int
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws QueryFilterInvalidParamException
     * @throws QueueItemSaveException
     */
    public function saveWithCondition(QueueItem $queueItem, array $additionalWhere = []): int
    {
        $itemId = $queueItem->getId();

        if (null !== $itemId) {
            $this->updateQueueItem($queueItem, $additionalWhere);

            return $itemId;
        }

        return $this->save($queueItem);
    }

    /**
     * Inflates queue items.
     *
     * @param array $rawItems
     *
     * @return array
     */
    protected function inflateQueueItems(array $rawItems = []): array
    {
        $result = [];
        foreach ($rawItems as $rawItem) {
            $item = new QueueItem();
            /** @var array $data */
            $data = json_decode($rawItem['data'], true);
            $item->inflate($data);
            $item->setId((int)$rawItem['id']);
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Fetch queue IDs based on priority and limit
     *
     * @param int $priority
     * @param int $limit
     *
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    protected function getQueueIdsForExecution(int $priority, int $limit): array
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $connection = $entityManager->getConnection();

        $index = $this->getColumnIndexMap();
        $nameColumn = 'index_' . $index['queueName'];
        $statusColumn = 'index_' . $index['status'];
        $priorityColumn = 'index_' . $index['priority'];

        $queuedStatus = QueueItem::QUEUED;
        $inProgressStatus = QueueItem::IN_PROGRESS;

        $runningQueueNames = $connection->createQueryBuilder()
            ->select("DISTINCT $nameColumn")
            ->from(self::TABLE_NAME, 'queue')
            ->where("queue.$statusColumn = '$inProgressStatus'")
            ->executeQuery()
            ->fetchFirstColumn();

        $query = $connection->createQueryBuilder()
            ->select('MIN(queue.id) AS id')
            ->from(self::TABLE_NAME, 'queue')
            ->where("queue.$statusColumn = :queuedStatus")
            ->andWhere("queue.$priorityColumn = :priority")
            ->groupBy("queue.$nameColumn");

        $query->setParameter('queuedStatus', $queuedStatus);
        $query->setParameter('priority', IndexHelper::castFieldValue($priority, Index::INTEGER));

        if (count($runningQueueNames) > 0) {
            $query->andWhere("queue.$nameColumn NOT IN (:names)");
            $query->setParameter('names', $runningQueueNames, ArrayParameterType::STRING);
        }

        $result = $query->executeQuery()->fetchFirstColumn();
        sort($result);

        return array_slice($result, 0, $limit);
    }

    /**
     * @param array $ids
     * @param string $status
     *
     * @return void
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function batchStatusUpdate(array $ids, string $status): void
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->getEntityManager();

        $connection = $entityManager->getConnection();
        $index = $this->getColumnIndexMap();
        $statusColumn = 'index_' . $index['status'];
        $lastUpdateColumn = 'index_' . $index['lastUpdateTimestamp'];

        $query = $connection->createQueryBuilder()
            ->update(self::TABLE_NAME, 'queue')
            ->set("queue.$statusColumn", ':status')
            ->set("queue.$lastUpdateColumn", (string)time())
            ->where('queue.id IN(:ids)');

        $query->setParameter('ids', $ids, ArrayParameterType::INTEGER);
        $query->setParameter('status', $status);

        $query->executeQuery();
    }

    /**
     * @param QueueItem $queueItem
     * @param array $additionalWhere
     *
     * @return void
     *
     * @throws QueueItemSaveException
     * @throws QueryFilterInvalidParamException
     */
    protected function updateQueueItem(QueueItem $queueItem, array $additionalWhere): void
    {
        $filter = new QueryFilter();
        $filter->where('id', Operators::EQUALS, $queueItem->getId());

        foreach ($additionalWhere as $name => $value) {
            if (null !== $value) {
                $filter->where($name, Operators::EQUALS, $value);
            }
        }

        /** @var QueueItem $item */
        $item = $this->selectOne($filter);
        if (null == $item) {
            throw new QueueItemSaveException("Cannot update queue item with id {$queueItem->getId()}.");
        }

        $this->update($queueItem);
    }

    /**
     * Retrieves index column map.
     *
     * @return array
     */
    protected function getColumnIndexMap(): array
    {
        $queueItem = new QueueItem();

        return IndexHelper::mapFieldsToIndexes($queueItem);
    }
}
