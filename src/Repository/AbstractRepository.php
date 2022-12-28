<?php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use ReflectionClass;

abstract class AbstractRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry, string $entity)
    {
        parent::__construct($registry, $entity);
    }

    public function getQbAll(): QueryBuilder {
        $entityName = explode('\\', $this->_entityName)[2];
        return $this->createQueryBuilder(strtolower($entityName));
    }

    /**
     * This function allow add filter date range by query of dashboard
     *
     * @param string $dateProperty
     * @param \Closure $queryModifier
     * @param \Closure $resultModifier
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @return array
     */
    protected function getDateFilteredResult(QueryBuilder $queryBuilder, \Closure $resultModifier, string $dateProperty, ?\DateTime $startDate = null, ?\DateTime $endDate = null): array
    {
        // Get entityName
        $entityClass = strtolower((new ReflectionClass($this->getClassName()))->getShortName());
        
        isset($endDate) ? $endDate->modify('+1 day'): null;

        // Filter result by date
        if (isset($startDate) && isset($endDate)) {
            // If both startDate and endDate are provided, filter results between these dates
            $queryBuilder->andWhere("$entityClass.$dateProperty BETWEEN :startDate AND :endDate")
                ->setParameter('startDate', $startDate)
                ->setParameter('endDate', $endDate);
        } elseif (isset($startDate)) {
             // If only startDate is provided, filter results from that date onwards
            $queryBuilder->andWhere("$entityClass.$dateProperty >= :startDate")
                ->setParameter('startDate', $startDate);
        } elseif (isset($endDate)) {
            // If only endDate is provided, filter results up to that date
            $queryBuilder->andWhere("$entityClass.$dateProperty <= :endDate")
                ->setParameter('endDate', $endDate);
        } 

        // Add filter date to query
        // $queryModifier($queryBuilder);

        // Add type of result which would like return query
        $result = $resultModifier($queryBuilder->getQuery());
        return $result;
    }
}
