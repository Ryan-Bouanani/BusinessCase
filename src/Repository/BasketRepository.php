<?php

namespace App\Repository;

use App\Entity\Basket;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

// stats api
    public function nbBasket(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array 
    {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }
    
        $nbBasket = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbBasket')
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
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
        ->select('(SUM(contentSC.price * contentSC.quantity) / COUNT(DISTINCT basket)) AS prixMoyenPanier')
        ->join('basket.contentShoppingCarts', 'contentSC')  
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->getQuery()
        ->getOneOrNullResult();

        $query['prixMoyenPanier'] = round($query['prixMoyenPanier'], 2);
        return $query;
    }
    
    
    public function percentageAbandonedBasket(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $subQuery = $this->createQueryBuilder('basket')
        ->select('COUNT(basket)') 
        ->join("basket.status", "status")
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->andWhere("status.name = 'Annuler'")
        ->getQuery()
        ->getResult();

        $query = $this->createQueryBuilder('basket')
        ->select('((:subQuery / COUNT(basket)) * 100) AS PourcentagePanierAbandonnes') 
        ->setParameter('subQuery', $subQuery) 
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->getQuery()
        ->getOneOrNullResult();

        $query['PourcentagePanierAbandonnes'] = round($query['PourcentagePanierAbandonnes'],2);
        return $query;
    }
    
    // todo faire un if pour chaque date et non les 2 en meme temps
    // public function orderConversionPercentage(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

    //     if ($beginDate === null || $endDate === null) {
    //         $beginDate = new DateTime('2018-01-01');
    //         $endDate = new DateTime('now');
    //     }

    //     $subQuery = $this->createQueryBuilder('basket')
    //     ->select('COUNT(basket)')  
    //     ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
    //     ->setParameter('beginDate', $beginDate)
    //     ->setParameter('endDate', $endDate)
    //     ->getQuery()
    //     ->getOneOrNullResult();

    //     $query = $this->createQueryBuilder('basket')
    //     ->select('((COUNT(basket) / :subQuery) * 100) AS PourcentagePanierTransformerEnCommande') 
    //     ->setParameter('subQuery', $subQuery) 
    //     ->join("basket.status", "status")
    //     ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
    //     ->setParameter('beginDate', $beginDate)
    //     ->setParameter('endDate', $endDate)
    //     ->andWhere("status.name = 'Valider'")
    //     ->getQuery()
    //     ->getOneOrNullResult();

    //     $query['PourcentagePanierTransformerEnCommande'] = round($query['PourcentagePanierTransformerEnCommande'], 2);
    //     return $query;
    // }

    public function nbOrder(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array 
    {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }
    
        $nbOrder = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS NbOrder')
            ->join("basket.status", "status")
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->andWhere("status.name = 'Valider'")
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
        ->select('SUM(contentShoppingCart.price * contentShoppingCart.quantity) AS ChiffreAffaire')
        ->join('basket.contentShoppingCarts', 'contentShoppingCart')
        ->join("basket.status", "status")
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->andWhere("status.name = 'Valider'")
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
            // todo ya plusieurs image en main par produit sa fausse les stats il compte plusieurs fois du coup
            ->select('product.title as NomProduit','image.path as Image' , 'SUM(contentSC.quantity) as NbVendue', 'SUM(contentSC.price * contentSC.quantity) as MontantTotalVendues')
            ->join('basket.contentShoppingCarts', 'contentSC')
            ->join('contentSC.product', 'product')
            ->join("basket.status", "status")
            ->join('product.images', 'image')
            ->groupBy('product')
            ->orderBy('MontantTotalVendues', 'DESC')
            ->where('image.isMain = true')
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            // todo voir si faire un join de status et plus performant que mettre le nb du status souhaitée
            ->andWhere('status.name = "Valider"')
            ->setMaxResults(8)
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
            ->join('basket.status', 'status')
            ->where("status.name = 'Valider'")
            ->groupBy('basket.customer')
            ->having('COUNT(basket) = 1')
            ->getQuery()
            ->getResult();

        // selectionne les commandes avec clients ayant commandés qu'une seule fois sur la periode selectionnée
        $newCustomerByDate = $this->createQueryBuilder('basket')
            ->select('customer.id')  
            ->join('basket.customer', 'customer')
            ->join('basket.status', 'status')
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->andWhere("status.name = 'Valider'")
            ->groupBy('basket.customer')
            ->having('COUNT(basket) = 1')
            ->getQuery()
            ->getResult();

            // selectionne les commandes avec clients existant
            // $existingCustomer = $this->createQueryBuilder('basket')
            //     ->select('customer.id')  
            //     ->join('basket.customer', 'customer')
            //     ->join('basket.status', 'status')
            //     ->andWhere("status.name = 'Valider'")
            //     ->groupBy('basket.customer')
            //     // ->having('COUNT(basket) > 1')
            //     ->getQuery()
            //     ->getResult();
    
        // selectionne le nombre de commande
        $query2 = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS OrderWithExistingCustomer') 
            ->join('basket.customer', 'customer') 
            ->join("basket.status", "status")
            ->andWhere("status.name = 'Valider'")
            ->getQuery()
            ->getOneOrNullResult();

            $query = $this->createQueryBuilder('basket')
            ->select('COUNT(basket) AS RecurrenceOrderCustomer') 
            ->join('basket.customer', 'customer') 
            ->join("basket.status", "status")
            ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            // ->setParameter('query2', $query2) 
            ->andWhere('customer IN (:newCustomer)')
            ->andWhere('customer IN (:newCustomerByDate)')
            ->setParameter('newCustomer', $newCustomer) 
            ->setParameter('newCustomerByDate', $newCustomerByDate)
            ->andWhere("status.name = 'Valider'")
            ->getQuery()
            ->getOneOrNullResult();

        $query['RecurrenceOrderCustomer'] = round((($query['RecurrenceOrderCustomer'] / $query2['OrderWithExistingCustomer']) * 100), 2);

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
