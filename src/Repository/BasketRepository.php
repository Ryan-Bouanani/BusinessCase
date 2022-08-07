<?php

namespace App\Repository;

use App\Entity\Basket;
use App\Entity\Order;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @extends ServiceEntityRepository<Basket>
 *
 * @method Basket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Basket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Basket[]    findAll()
 * @method Basket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BasketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Basket::class);
    }

    public function add(Basket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Basket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    // public function nbBaskeet(?\DateTime $startDate = null, ?\DateTime $endDate = null): int 
    // {
    //     if ($startDate === null || $endDate === null) {
    //         $endDate = new DateTime('now');
    //         $startDate = new DateTime('2020-01-01');
    //     }
    //     $qb = $this->createQueryBuilder('b');
    //     $qb->select('count(b)')
    //         ->where('b.status = :status')
    //         ->setParameter('status', BasketStatusEnum::STATUS_CANCELED)
    //         ->andWhere('basket.dateCreation BETWEEN :startDate AND :endDate')
    //         ->setParameter('startDate', $startDate)
    //         ->setParameter('endDate', $endDate);
    //     $query = $qb->getQuery();
    //     $nbBasket = $query->getSingleScalarResult();
    //     return $nbBasket;
    // }

    public function nbBasket(?\DateTime $beginDate = null, ?\DateTime $endDate = null): int 
    {
        // return $this->count([]);
        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }
    
        $nbBasket = $this->createQueryBuilder('b')
            ->select('COUNT(b)')
            ->where('b.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult()
            ;
            return $nbBasket;

    }

    public function averagePriceBasket()  {
        $query = $this->createQueryBuilder('b')
        ->select('SUM(c.price * c.quantity) / COUNT(DISTINCT b)')
        ->join('b.contentShoppingCarts', 'c')  
        ->getQuery()
        ->getResult();
        dump($query);
        return $query;
    }
    
// WHERE command.created_at BETWEEN '2022-06-01' AND '2022-07-11'
// AND command.status IN (200, 300)
    public function percentageAbandonedBasket() {
        $subQuery = $this->createQueryBuilder('b')
        ->select('(COUNT(b))') 
        ->join(Order::class, 'o')  
        ->where('b != o')   
        ->getQuery()
        ->getResult();
        $query = $this->createQueryBuilder('b')
        ->select('(:subQuery / (COUNT(b))) * 100 AS PourcentagePanierAbandonnes') 
        ->setParameter('subQuery', $subQuery) 
        ->getQuery()
        ->getResult();

        return $query;
    }

    public function orderConversionPercentage() {
        $subQuery = $this->createQueryBuilder('b')
        ->select('(COUNT(o))') 
        ->join(Order::class, 'o', Join::WITH, 'o.basket = b')   
        ->where('b = o')  
        ->getQuery()
        ->getResult();
        $query = $this->createQueryBuilder('b')
        ->select('(:subQuery / (COUNT(b))) * 100 AS PourcentagePanierTransformerEnCommande') 
        ->setParameter('subQuery', $subQuery) 
        ->getQuery()
        ->getResult();

        return $query;
    }


//    /**
//     * @return Basket[] Returns an array of Basket objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Basket
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
