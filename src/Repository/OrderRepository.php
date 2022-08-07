<?php

namespace App\Repository;

use App\Entity\Order;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function add(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    // public function nbOrder(): int
    // {
    //     return $this->count([]);

    // }
    public function nbOrder(?\DateTime $beginDate = null, ?\DateTime $endDate = null): int 
    {
        // return $this->count([]);
        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }
    
        $nbOrder = $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->join('o.basket', 'b')
            ->where('b.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getSingleScalarResult()
            ;
            return $nbOrder;

    }
    

    public function turnover() {
        $query = $this->createQueryBuilder('order')
        ->select('SUM(contentShoppingCart.price * contentShoppingCart.quantity)')
        ->join('order.basket', 'basket')
        ->join('basket.contentShoppingCarts', 'contentShoppingCart')
        ->getQuery()
        ->getResult();
        return $query;
    }

    public function bestSellingProduct() {
        $query = $this->createQueryBuilder('order')
        ->select('p.title as NomProduit','i.path as Image' , 'SUM(c.quantity) as NbVendue', 'SUM(c.price * c.quantity) as MontantTotalVendues')
        ->join('order.basket', 'basket')
        ->join('basket.contentShoppingCarts', 'c')
        ->join('c.product', 'p')
        ->join('p.images', 'i')
        ->groupBy('p')
        ->orderBy('SUM(c.price * c.quantity)', 'DESC')
        ->where('i.isMain = true')
        ->getQuery()
        ->getResult();
        return $query;
    }


//    /**
//     * @return Order[] Returns an array of Order objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Order
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
