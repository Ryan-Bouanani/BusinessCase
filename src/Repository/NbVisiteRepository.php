<?php

namespace App\Repository;

use App\Entity\NbVisite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NbVisite>
 *
 * @method NbVisite|null find($id, $lockMode = null, $lockVersion = null)
 * @method NbVisite|null findOneBy(array $criteria, array $orderBy = null)
 * @method NbVisite[]    findAll()
 * @method NbVisite[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NbVisiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NbVisite::class);
    }

    public function add(NbVisite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NbVisite $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // todo enlever le 'e' de visite
    public function nbVisit(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new \DateTime('2018-01-01');
            $endDate = new \DateTime('now');
        }

        $query = $this->createQueryBuilder('visit')
        ->select('COUNT(visit) AS NbVisit') 
        ->where('visit.visitAt BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->getQuery()
        ->getOneOrNullResult();

        return $query;
    }

//    /**
//     * @return NbVisite[] Returns an array of NbVisite objects
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

//    public function findOneBySomeField($value): ?NbVisite
//    {
//        return $this->createQueryBuilder('n')
//            ->andWhere('n.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
