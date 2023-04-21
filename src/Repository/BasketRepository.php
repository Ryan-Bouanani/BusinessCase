<?php

namespace App\Repository;

use App\Entity\Basket;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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

    public function findLastOrderWithCustomer(int $id, $limit = null): array|null
    {
        $oldBasket = $this->createQueryBuilder('basket')
            ->select('basket')
            ->where('basket.status IS NOT NULL')
            ->andWhere('basket.customer = :id')
            ->leftJoin('basket.customer', 'customer')
            ->orderBy('basket.dateCreated', 'DESC')
            ->setParameter('id', $id)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
        ;
        return $oldBasket;
    }











// stats api

    public function nbBasketAndOrders(?\DateTime $startDate = null, ?\DateTime $endDate = null): array 
    {
        $queryModifier = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbBasketAndOrders')
        ;

        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getOneOrNullResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $nbBasket = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
        
        // Check if result is null
        if (empty($nbBasket['NbBasketAndOrders'])) {
            $nbBasket['NbBasketAndOrders'] = 0;
        }
        return $nbBasket;
    }

    public function nbBasket(?\DateTime $startDate = null, ?\DateTime $endDate = null): array 
    {

        $queryModifier = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbBasket')
            ->andWhere("basket.status IS NULL")
        ;

        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getOneOrNullResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $nbBasket = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
        
         // Check if result is null
        if (empty($nbBasket['NbBasket'])) {
            $nbBasket['NbBasket'] = 0;
        }
        return $nbBasket;
    }

    public function averagePriceBasket(?\DateTime $startDate = null, ?\DateTime $endDate = null): array  {

        $queryModifier = $this->createQueryBuilder('basket')
            ->select('(SUM(contentSC.price * contentSC.quantity) / COUNT(DISTINCT basket)) AS AveragePriceBasket')
            ->join('basket.contentShoppingCarts', 'contentSC')
        ;

        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getOneOrNullResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $averagePriceBasket = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
       
        // Check if result is null
        if (empty($averagePriceBasket['AveragePriceBasket'])) {
            $averagePriceBasket['AveragePriceBasket'] = 0;
        }
        return $averagePriceBasket;
    }
    
    // selectionne les paniers qui qui ont étés crée il y'a plus de 2 heures et qui n'ont toujours pas été convertis en commandes
    public function abandonedBasket(?\DateTime $startDate = null, ?\DateTime $endDate = null): array {

        $queryModifier = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbAbandonedBasket') 
            ->leftJoin("basket.status", "status")
            ->where("CURRENT_TIMESTAMP() > DATE_ADD(basket.dateCreated, 2, 'HOUR')")
            ->andWhere("status IS NULL")
        ;
        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getOneOrNullResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $NbAbandonedBasket = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);

        // Check if result is null
        if (empty($NbAbandonedBasket['NbAbandonedBasket'])) {
            $NbAbandonedBasket['NbAbandonedBasket'] = 0;
        }
        return $NbAbandonedBasket;
    }
    
    // todo faire un if pour chaque date et non les 2 en meme temps
    public function nbOrder(?\DateTime $startDate = null, ?\DateTime $endDate = null): array {

        $queryModifier = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbOrder')
            ->leftJoin("basket.status", "status")
            ->andWhere("status IS NOT NULL")
        ;

        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getOneOrNullResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $nbOrder = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
        
        // Check if result is null
        if (empty($nbOrder['NbOrder'])) {
            $nbOrder['NbOrder'] = 0;
        }
        return $nbOrder;     
    }
    

    public function turnover(?\DateTime $startDate = null, ?\DateTime $endDate = null) {

        $queryModifier = $this->createQueryBuilder('basket')
            ->select('SUM(contentShoppingCart.price * contentShoppingCart.quantity) AS Turnover')
            ->join('basket.contentShoppingCarts', 'contentShoppingCart')
            ->leftJoin("basket.status", "status")
            ->Where("status IS NOT NULL")
            ->andWhere("status.name != 'Remboursée'");
        ;

        $resultModifier = function(Query $query) {
             // Return the desired result
            return $query->getOneOrNullResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $turnover = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
        ;
        // Check if result is null
        if (empty($turnover['Turnover'])) {
            $turnover['Turnover'] = 0;
        }
        return $turnover;
    }

    public function bestSellingProduct(?\DateTime $startDate = null, ?\DateTime $endDate = null): array {

        $queryModifier = $this->createQueryBuilder('basket')
            ->select('product.name AS NameProduct','image.path AS Image', 'SUM(contentSC.quantity) AS NbSold', 'SUM(contentSC.price * contentSC.quantity) AS TotalAmountSold')
            ->join('basket.contentShoppingCarts', 'contentSC')
            ->join('contentSC.product', 'product')
            ->leftJoin("basket.status", "status")
            ->join('product.images', 'image')
            ->groupBy('product')
            ->orderBy('NbSold', 'DESC')
            ->addOrderBy('TotalAmountSold', 'DESC')
            ->where('image.isMain = true')
            ->andWhere("status IS NOT NULL")
            ->andWhere("status.name != 'Remboursée'")
            ->setMaxResults(7)
        ;

        $resultModifier = function(Query $query) {
             // Return the desired result
            return $query->getResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $bestSellingProduct = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
        ;
        return $bestSellingProduct;
    }

    
    public function recurrenceOrderCustomer(?\DateTime $startDate = null, ?\DateTime $endDate = null) {
        // $startDate = new DateTime('2022-12-14');
        // $endDate = new DateTime('2022-12-16');
        // $startDate = $startDate->format('Y-m-d');
        // $endDate = $endDate->format('Y-m-d');
        
        // selectionne les nouveaux clients ayant commandés qu'une seule fois
            $newCustomer = $this->createQueryBuilder('basket')
                ->select('customer.id AS IdNewClient')  
                ->join('basket.customer', 'customer')
                ->leftJoin('basket.status', 'status')
                ->where("status IS NOT NULL")
                ->groupBy('basket.customer')
                ->having('COUNT(basket) = 1')
                ->getQuery()
                ->getResult()
            ;
        // 
        // selectionne les commandes avec clients ayant commandés qu'une seule fois sur la periode selectionnée
            $queryModifier = $this->createQueryBuilder('basket')
                ->select('customer.id AS IdNewClient')  
                ->join('basket.customer', 'customer')
                ->leftJoin('basket.status', 'status')
                ->where("status IS NOT NULL")
                ->groupBy('basket.customer')
                ->having('COUNT(basket) = 1')
            ;
            $resultModifier = function(Query $query) {
                // Return the desired result
                return $query->getResult();
            };
            // Call the getDateFilteredResult method for add filter date is in query
            $newCustomerByDate = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
            ;
        // 

        // Sélectionne le nombre de commandes avec nouveau clients (hors et pendant les date)
            // $queryModifier = $this->createQueryBuilder('basket')
            //     ->select('COUNT(basket) AS NbOrderNewCustomer') 
            //     ->join('basket.customer', 'customer') 
            //     ->leftJoin("basket.status", "status")
            //     // ->setParameter('query2', $query2) 
            //     ->where('customer IN (:newCustomer)')
            //     ->andWhere('customer IN (:newCustomerByDate)')
            //     ->setParameter('newCustomer', $newCustomer) 
            //     ->setParameter('newCustomerByDate', $newCustomerByDate)
            //     ->where("status IS NOT NULL")
            // ;
            // $resultModifier = function(Query $query) {
            //     // Return the desired result
            //     return $query->getOneOrNullResult();
            // };
            // // Call the getDateFilteredResult method for add filter date is in query
            // $query = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
        // 

        // Sélectionne le nombre de commandes
        $nbOrder = $this->nbOrder($startDate, $endDate);

        // selectionne les clients déja existant
        $existingCustomer = $this->createQueryBuilder('basket')
            ->select('customer.id')  
            ->join('basket.customer', 'customer')
            ->leftJoin('basket.status', 'status')
            ->where("status IS NOT NULL")
            ->groupBy('basket.customer')
            ->having('COUNT(basket) > 1')
            ->getQuery()
            ->getResult()
        ;

        // Sélectionne le nombre de commandes avec clients déja existant
            $queryModifier = $this->createQueryBuilder('basket')
                ->select('COUNT(basket) AS OrderWithExistingCustomer') 
                ->join('basket.customer', 'customer') 
                ->leftJoin("basket.status", "status")
                ->Where("status IS NOT NULL")
                ->andWhere('customer IN (:existingCustomer)')
                ->setParameter('existingCustomer', $existingCustomer)
            ;
            $resultModifier = function(Query $query) {
                // Return the desired result
                return $query->getOneOrNullResult();
            };
            // Call the getDateFilteredResult method for add filter date is in query
            $query2 = $this->getDateFilteredResult($queryModifier, $resultModifier, 'dateCreated', $startDate, $endDate);
            ;
        // 

        if ($nbOrder['NbOrder'] > 0) {
            $recurrenceOrderCustomer['RecurrenceOrderCustomer'] = round((($query2['OrderWithExistingCustomer'] / $nbOrder['NbOrder']) * 100), 2);
        } else {
            $recurrenceOrderCustomer['RecurrenceOrderCustomer'] = 0;
        }
        return $recurrenceOrderCustomer;
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
