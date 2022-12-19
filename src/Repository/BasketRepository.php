<?php

namespace App\Repository;

use App\Entity\Basket;
use DateTime;
use Doctrine\ORM\QueryBuilder;
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
class BasketRepository extends AbstractRepository
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


    public function findBasketWithCustomer(int $id): array
    {
        return $this->createQueryBuilder('basket')
            ->select('basket')
            ->where('basket.status IS NULL')
            ->andWhere('basket.customer = :id')
            ->leftJoin('basket.customer', 'customer')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
            ;
    }

    public function findLastBasketWithCustomer(int $id, $limit = null): array|null
    {
        $oldBasket = $this->createQueryBuilder('basket')
            ->select('basket')
            ->where('basket.status IS NOT NULL')
            ->andWhere('basket.customer = :id')
            ->leftJoin('basket.customer', 'customer')
            ->orderBy('basket.dateCreated', 'ASC')
            ->setParameter('id', $id)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
            ;
            return $oldBasket;
    }











// stats api

    public function nbBasketAndOrders(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array 
    {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $nbBasket = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbBasketAndOrders')
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->leftJoin("basket.status", "status")
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getOneOrNullResult();
            ;
            return $nbBasket;
    }

    public function nbBasket(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array 
    {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }
    
        $nbBasket = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbBasket')
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->leftJoin("basket.status", "status")
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->andWhere("status IS NULL")
            ->getQuery()
            ->getOneOrNullResult();
            ;
            return $nbBasket;
    }

    public function averagePriceBasket(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array  {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $query = $this->createQueryBuilder('basket')
        ->select('(SUM(contentSC.price * contentSC.quantity) / COUNT(DISTINCT basket)) AS AveragePriceBasket')
        ->join('basket.contentShoppingCarts', 'contentSC')  
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->getQuery()
        ->getOneOrNullResult()
        ;
        return $query;
    }
    
    // selectionne les paniers qui qui ont étés crée il y'a plus de 2 heures et qui n'ont toujours pas été convertis en commandes
    public function abandonedBasket(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $query = $this->createQueryBuilder('basket')
        ->select('COUNT(basket) AS NbAbandonedBasket') 
        ->leftJoin("basket.status", "status")
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->andWhere("CURRENT_TIMESTAMP() > DATE_ADD(basket.dateCreated, 2, 'HOUR')")
        ->andWhere("status IS NULL")
        ->getQuery()
        ->getOneOrNullResult();

        return $query;
    }
    
    // todo faire un if pour chaque date et non les 2 en meme temps
    public function nbOrder(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array 
    {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }
    
        $nbOrder = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbOrder')
            ->leftJoin("basket.status", "status")
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->andWhere("status IS NOT NULL")
            ->getQuery()
            ->getOneOrNullResult();
            ;
            return $nbOrder;     
        ;
    }
    

    public function turnover(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $query = $this->createQueryBuilder('basket')
        ->select('SUM(contentShoppingCart.price * contentShoppingCart.quantity) AS Turnover')
        ->join('basket.contentShoppingCarts', 'contentShoppingCart')
        ->leftJoin("basket.status", "status")
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->andWhere("status IS NOT NULL")
        ->andWhere("status.name != 'Remboursée'")
        ->getQuery()
        ->getOneOrNullResult();
        return $query;
    }

    public function bestSellingProduct(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $query = $this->createQueryBuilder('basket')
            ->select('product.name AS NameProduct','image.path AS Image', 'SUM(contentSC.quantity) AS NbSold', 'SUM(contentSC.price * contentSC.quantity) AS TotalAmountSold')
            ->join('basket.contentShoppingCarts', 'contentSC')
            ->join('contentSC.product', 'product')
            ->leftJoin("basket.status", "status")
            ->join('product.images', 'image')
            ->groupBy('product')
            ->orderBy('NbSold', 'DESC')
            ->addOrderBy('TotalAmountSold', 'DESC')
            ->where('image.isMain = true')
            ->andWhere('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            // todo voir si faire un join de status et plus performant que mettre le nb du status souhaitée
            ->andWhere("status IS NOT NULL")
            ->andWhere("status.name != 'Remboursée'")
            ->setMaxResults(7)
            ->getQuery()
            ->getResult()
        ;
        return $query;
    }


    public function recurrenceOrderCustomer(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {
        
        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        
        // selectionne les nouveaux clients ayant commandés qu'une seule fois
        $newCustomer = $this->createQueryBuilder('basket')
            ->select('customer.id AS IdNewClient')  
            ->join('basket.customer', 'customer')
            ->leftJoin('basket.status', 'status')
            ->where("status IS NOT NULL")
            ->groupBy('basket.customer')
            ->having('COUNT(basket) = 1')
            ->getQuery()
            ->getResult();

        // selectionne les commandes avec clients ayant commandés qu'une seule fois sur la periode selectionnée
        $newCustomerByDate = $this->createQueryBuilder('basket')
            ->select('customer.id AS IdNewClient')  
            ->join('basket.customer', 'customer')
            ->leftJoin('basket.status', 'status')
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->andWhere("status IS NOT NULL")
            ->groupBy('basket.customer')
            ->having('COUNT(basket) = 1')
            ->getQuery()
            ->getResult()
        ;

        // selectionne les commandes avec clients existant
        $existingCustomer = $this->createQueryBuilder('basket')
            ->select('customer.id')  
            ->join('basket.customer', 'customer')
            ->leftJoin('basket.status', 'status')
            ->andWhere("status IS NOT NULL")
            ->groupBy('basket.customer')
            ->having('COUNT(basket) > 1')
            ->getQuery()
            ->getResult()
        ;
    
        // selectionne le nombre de commandes
        $query2 = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS OrderWithExistingCustomer') 
            ->join('basket.customer', 'customer') 
            ->leftJoin("basket.status", "status")
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->andWhere("status IS NOT NULL")
            // ->andWhere('customer IN (:existingCustomer)')
            // ->setParameter('existingCustomer', $existingCustomer)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        $query = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS RecurrenceOrderCustomer') 
            ->join('basket.customer', 'customer') 
            ->leftJoin("basket.status", "status")
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            // ->setParameter('query2', $query2) 
            ->andWhere('customer IN (:newCustomer)')
            ->andWhere('customer IN (:newCustomerByDate)')
            ->setParameter('newCustomer', $newCustomer) 
            ->setParameter('newCustomerByDate', $newCustomerByDate)
            ->andWhere("status IS NOT NULL")
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if ($query2['OrderWithExistingCustomer'] == 0) {
            $query['RecurrenceOrderCustomer'] = round(($query['RecurrenceOrderCustomer'] * 100), 2);
        } else {
            $query['RecurrenceOrderCustomer'] = round((($query['RecurrenceOrderCustomer'] / $query2['OrderWithExistingCustomer']) * 100), 2);
        }
        return $query;


    }



    public function getQbAll(): QueryBuilder {
        $qb = parent::getQbAll();
        return $qb->select('basket','status')
        ->join('basket.status', 'status')
        ->leftJoin('basket.address', 'address')
        ->join('basket.meanOfPayment', 'meanOfPayment')
        ->where('basket.status IS NOT NULL')
        ->groupBy('basket')
        ->orderBy('basket.id', 'ASC')
        ;
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
