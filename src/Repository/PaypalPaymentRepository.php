<?php

namespace App\Repository;

use App\Entity\PaypalPayment;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaypalPayment>
 *
 * @method PaypalPayment|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaypalPayment|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaypalPayment[]    findAll()
 * @method PaypalPayment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaypalPaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaypalPayment::class);
    }

    public function add(PaypalPayment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(PaypalPayment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return PaypalPayment[] Returns an array of PaypalPayment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?PaypalPayment
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
