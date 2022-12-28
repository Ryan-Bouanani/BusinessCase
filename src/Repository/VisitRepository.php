<?php

namespace App\Repository;

use App\Entity\Visit;
use DateInterval;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Visit>
 *
 * @method Visit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visit[]    findAll()
 * @method Visit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VisitRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visit::class);
    }

    public function add(Visit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Visit $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function nbVisit(?\DateTime $startDate = null, ?\DateTime $endDate = null): array {

        $queryModifier = $this->createQueryBuilder('visit')
            ->select('COUNT(visit) AS NbVisit') 
        ;

        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getOneOrNullResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $nbVisit = $this->getDateFilteredResult($queryModifier, $resultModifier, 'visitAt', $startDate, $endDate);
        
        // Check if result is null
        if (empty($nbVisit['NbVisit'])) {
            $nbVisit['NbVisit'] = 0;
        }
        return $nbVisit;
    }
    public function nbVisitArray(?\DateTime $startDate = null, ?\DateTime $endDate = null): array {

        // If startDate is not provided, set it to the earliest visit date in the database
        if (!$startDate) {
            $startDate = $this->createQueryBuilder('visit')
                ->select('MIN(visit.visitAt)')
                ->getQuery()
                ->getSingleScalarResult();
            $startDate = new \DateTime($startDate);
        }
        // If endDate is not provided, set it to the current date and time
        if (!$endDate) {
            $endDate = new \DateTime('NOW');
        }

        $queryModifier = $this->createQueryBuilder('visit');
        $queryModifier->select('COUNT(visit) AS NbVisit');
    
        // Calculate the time difference between start date and end date
        // $interval = new DateInterval('P1M');
        // $startDate->add($interval) > $endDate;
        $diff = $startDate->diff($endDate);
        // return [$diff, $startDate, $endDate, $diff->y >= 4];
        if ($diff->days <= 30) {
            // If time between start date and end date is less than or equal to 1 month, group by day
            $queryModifier->addSelect('SUBSTRING(visit.visitAt, 1, 10) as VisiteDate');
        } 
        // elseif ($diff->days <= 120) {
        //         // If time between start date and end date is more than 4 months, group by week
        //         $queryModifier->addSelect("WEEK(visit.visitAt) as VisiteDate");
        // } 
        elseif ($diff->y >= 4) {
                // If time between start date and end date is more than 4 months, group by week
                $queryModifier->addSelect("YEAR(visit.visitAt) as VisiteDate");
        } 
        else {
            // If time between start date and end date is more than 4 years, group by year
            $queryModifier->addSelect('SUBSTRING(visit.visitAt, 1, 7) as VisiteDate');
        }
       
        $queryModifier ->groupBy("VisiteDate")
            ->orderBy("VisiteDate", "ASC");

        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $nbVisit = $this->getDateFilteredResult($queryModifier, $resultModifier, 'visitAt', $startDate, $endDate);
        
        return $nbVisit;
    }

//    /**
//     * @return Visit[] Returns an array of Visit objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('n.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Visit
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
