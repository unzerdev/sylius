<?php

namespace SyliusUnzerPlugin\Repositories;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\QueryBuilder;
use Exception;
use SyliusUnzerPlugin\Entity\UnzerBaseEntity;
use Unzer\Core\Infrastructure\ORM\Entity;
use Unzer\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use Unzer\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use Unzer\Core\Infrastructure\ORM\QueryFilter\Operators;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryCondition;
use Unzer\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use Unzer\Core\Infrastructure\ORM\Utility\IndexHelper;

/**
 * Class BaseRepository.
 *
 * @package SyliusUnzerPlugin\Repositories
 */
class BaseRepository implements RepositoryInterface
{
    /**
     * Fully qualified name of this class.
     */
    public const THIS_CLASS_NAME = __CLASS__;

    /**
     * Name of the base entity table in database.
     */
    public const TABLE_NAME = 'unzer_entity';

    /**
     * @var string
     */
    protected string $entityClass;

    /**
     * @var string
     */
    protected static string $doctrineModel = UnzerBaseEntity::class;

    /**
     * @var ?EntityManagerInterface
     */
    private static ?EntityManagerInterface $entityManager = null;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return ?EntityManagerInterface
     */
    public function getEntityManager(): ?EntityManagerInterface
    {
        return self::$entityManager;
    }

    /**
     * @param EntityManagerInterface $entityManager
     *
     * @return void
     */
    public static function setEntityManager(EntityManagerInterface $entityManager): void
    {
        self::$entityManager = $entityManager;
    }

    /**
     * Returns full class name.
     *
     * @return string
     */
    public static function getClassName(): string
    {
        return static::THIS_CLASS_NAME;
    }

    /**
     * @param string $entityClass
     *
     * @return void
     */
    public function setEntityClass(string $entityClass): void
    {
        $this->entityClass = $entityClass;
    }

    /**
     * @throws QueryFilterInvalidParamException
     */
    public function select(QueryFilter $filter = null): array
    {
        $query = $this->getBaseDoctrineQuery($filter);

        return $this->getResult($query);
    }

    /**
     * @throws QueryFilterInvalidParamException
     */
    public function selectOne(QueryFilter $filter = null): ?Entity
    {
        $query = $this->getBaseDoctrineQuery($filter);
        $query->setMaxResults(1);

        $result = $this->getResult($query);

        return !empty($result[0]) ? $result[0] : null;
    }

    /**
     * @throws OptimisticLockException
     * @throws ORMException
     */
    public function save(Entity $entity): int
    {
        /** @var UnzerBaseEntity $doctrineEntity */
        $doctrineEntity = new static::$doctrineModel;
        $id = $this->persistEntity($entity, $doctrineEntity);
        $entity->setId($id);

        return $id;
    }

    /**
     * @param Entity $entity
     * @param QueryFilter|null $queryFilter
     *
     * @return bool
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Entity $entity, QueryFilter $queryFilter = null): bool
    {
        $result = true;

        try {
            /** @var UnzerBaseEntity $doctrineEntity */
            $doctrineEntity = $this->getEntityManager()->find(static::$doctrineModel, $entity->getId());
            if (!$doctrineEntity) {
                return false;
            }

            $this->persistEntity($entity, $doctrineEntity);
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Entity $entity
     *
     * @return bool
     */
    public function delete(Entity $entity): bool
    {
        $result = true;

        try {
            $persistentEntity = $this->getEntityManager()->find(static::$doctrineModel, $entity->getId());
            if ($persistentEntity) {
                $this->getEntityManager()->remove($persistentEntity);
                $this->getEntityManager()->flush($persistentEntity);
            }
        } catch (Exception $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @throws NonUniqueResultException
     * @throws QueryFilterInvalidParamException
     * @throws NoResultException
     */
    public function count(QueryFilter $filter = null): int
    {
        $query = $this->getBaseDoctrineQuery($filter, true);

        return (int)$query->getQuery()->getSingleScalarResult();
    }

    /**
     * Retrieves query result.
     *
     * @param QueryBuilder $builder
     *
     * @return Entity[]
     */
    protected function getResult(QueryBuilder $builder): array
    {
        $doctrineEntities = $builder->getQuery()->getResult();

        $result = [];

        /** @var UnzerBaseEntity $doctrineEntity */
        foreach ($doctrineEntities as $doctrineEntity) {
            $entity = $this->unserializeEntity($doctrineEntity->getData());
            if ($entity) {
                $entity->setId($doctrineEntity->getId());
                $result[] = $entity;
            }
        }

        return $result;
    }

    /**
     * Persists entity.
     *
     * @param Entity $entity
     * @param UnzerBaseEntity $persistedEntity
     *
     * @return int
     *
     * @throws OptimisticLockException
     * @throws ORMException
     */
    protected function persistEntity(Entity $entity, UnzerBaseEntity $persistedEntity): int
    {
        $persistedEntity->setType($entity->getConfig()->getType());
        $indexValueMap = IndexHelper::transformFieldsToIndexes($entity);

        foreach ($indexValueMap as $index => $value) {
            $setterName = "setIndex{$index}";
            $persistedEntity->$setterName($value);
        }

        $persistedEntity->setData(json_encode($entity->toArray()));

        $this->getEntityManager()->persist($persistedEntity);
        $this->getEntityManager()->flush($persistedEntity);

        return $persistedEntity->getId();
    }

    /**
     * Unserialize ORM entity.
     *
     * @param string $data
     *
     * @return Entity
     */
    protected function unserializeEntity(string $data): Entity
    {
        $jsonEntity = json_decode($data, true);
        if (array_key_exists('class_name', $jsonEntity)) {
            $entity = new $jsonEntity['class_name'];
        } else {
            $entity = new $this->entityClass;
        }

        /** @var Entity $entity */
        $entity->inflate($jsonEntity);

        return $entity;
    }

    /**
     * @param QueryFilter|null $filter
     * @param $isCount
     *
     * @return QueryBuilder
     * @throws QueryFilterInvalidParamException
     */
    protected function getBaseDoctrineQuery(QueryFilter $filter = null, $isCount = false)
    {
        /** @var Entity $entity */
        $entity = new $this->entityClass;
        $type = $entity->getConfig()->getType();
        $indexMap = IndexHelper::mapFieldsToIndexes($entity);

        $query = $this->getEntityManager()->createQueryBuilder();
        $alias = 'p';
        $baseSelect = $isCount ? "count($alias.id)" : $alias;
        $query->select($baseSelect)
            ->from(static::$doctrineModel, $alias)
            ->where("$alias.type = '$type'");

        $groups = $filter !== null ? $this->buildConditionGroups($filter, $indexMap) : [];
        $queryParts = $this->getQueryParts($groups, $indexMap, $alias);

        $where = $this->generateWhereStatement($queryParts);
        if (!empty($where)) {
            $query->andWhere($where);
        }

        if ($filter) {
            $this->setLimit($filter, $query);
            $this->setOffset($filter, $query);
            $this->setOrderBy($filter, $indexMap, $alias, $query);
        }

        return $query;
    }

    /**
     * Sets limit.
     *
     * @param QueryFilter $filter
     * @param QueryBuilder $query
     */
    protected function setLimit(QueryFilter $filter, QueryBuilder $query): void
    {
        if ($filter->getLimit()) {
            $query->setMaxResults($filter->getLimit());
        }
    }

    /**
     * Sets offset.
     *
     * @param QueryFilter $filter
     * @param QueryBuilder $query
     */
    protected function setOffset(QueryFilter $filter, QueryBuilder $query): void
    {
        if ($filter->getOffset()) {
            $query->setFirstResult($filter->getOffset());
        }
    }

    /**
     * Sets order by.
     *
     * @param QueryFilter $filter
     * @param array $indexMap
     * @param $alias
     * @param QueryBuilder $query
     */
    protected function setOrderBy(QueryFilter $filter, array $indexMap, $alias, QueryBuilder $query): void
    {
        if ($filter->getOrderByColumn()) {
            $orderByColumn = $filter->getOrderByColumn();

            if ($orderByColumn === 'id' || !empty($indexMap[$orderByColumn])) {
                $columnName = $orderByColumn === 'id'
                    ? "$alias.id" : "$alias.index" . $indexMap[$orderByColumn];
                $query->orderBy($columnName, $filter->getOrderDirection());
            }
        }
    }

    /**
     * Generates where statement.
     *
     * @param array $queryParts
     *
     * @return string
     */
    protected function generateWhereStatement(array $queryParts): string
    {
        $where = '';

        foreach ($queryParts as $index => $part) {
            $subWhere = '';

            if ($index > 0) {
                $subWhere .= ' OR ';
            }

            $subWhere .= $part[0];
            $count = count($part);
            for ($i = 1; $i < $count; $i++) {
                $subWhere .= ' AND ' . $part[$i];
            }

            $where .= $subWhere;
        }

        return $where;
    }

    /**
     * Builds condition groups (each group is chained with OR internally, and with AND externally) based on query
     * filter.
     *
     * @param QueryFilter $filter Query filter object.
     * @param array $fieldIndexMap Map of property indexes.
     *
     * @return array Array of condition groups.
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function buildConditionGroups(QueryFilter $filter, array $fieldIndexMap): array
    {
        $groups = [];
        $counter = 0;
        $fieldIndexMap['id'] = 0;
        foreach ($filter->getConditions() as $condition) {
            if (!empty($groups[$counter]) && $condition->getChainOperator() === 'OR') {
                $counter++;
            }

            // Only index columns can be filtered.
            if (!array_key_exists($condition->getColumn(), $fieldIndexMap)) {
                throw new QueryFilterInvalidParamException("Field [{$condition->getColumn()}] is not indexed.");
            }

            $groups[$counter][] = $condition;
        }

        return $groups;
    }

    /**
     * Retrieves group query parts.
     *
     * @param array $conditionGroups
     * @param array $indexMap
     * @param string $alias
     *
     * @return array
     */
    protected function getQueryParts(array $conditionGroups, array $indexMap, string $alias): array
    {
        $parts = [];

        foreach ($conditionGroups as $group) {
            $subPart = [];

            foreach ($group as $condition) {
                $subPart[] = $this->getQueryPart($condition, $indexMap, $alias);
            }

            if (!empty($subPart)) {
                $parts[] = $subPart;
            }
        }

        return $parts;
    }

    /**
     * Retrieves query part.
     *
     * @param QueryCondition $condition
     * @param array $indexMap
     * @param string $alias
     *
     * @return string
     */
    protected function getQueryPart(QueryCondition $condition, array $indexMap, string $alias): string
    {
        $column = $condition->getColumn();

        if ($column === 'id') {
            return "$alias.id=" . $condition->getValue();
        }

        if (in_array($condition->getOperator(), [Operators::NOT_IN, Operators::IN], true)) {
            $values = array_map(function ($item) {
                if (is_string($item)) {
                    return "'$item'";
                }

                if (is_int($item)) {
                    $val = IndexHelper::castFieldValue($item, 'integer');
                    return "'{$val}'";
                }

                $val = IndexHelper::castFieldValue($item, 'double');

                return "'{$val}'";
            }, $condition->getValue());
            $part = "$alias.index" . $indexMap[$column] . ' ' . $condition->getOperator() . '(' . implode(',',
                    $values) . ')';
        } else {
            $part = "$alias.index" . $indexMap[$column] . ' ' . $condition->getOperator();
            if (!in_array($condition->getOperator(), [Operators::NULL, Operators::NOT_NULL], true)) {
                $part .= " '" . IndexHelper::castFieldValue($condition->getValue(), $condition->getValueType()) . "'";
            }
        }

        return $part;
    }
}
