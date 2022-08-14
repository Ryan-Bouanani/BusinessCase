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
    
    // todo faire un if pour chawue date et non les 2 en meme temps
    public function orderConversionPercentage(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $subQuery = $this->createQueryBuilder('basket')
        ->select('COUNT(basket)')  
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->getQuery()
        ->getOneOrNullResult();

        $query = $this->createQueryBuilder('basket')
        ->select('((COUNT(basket) / :subQuery) * 100) AS PourcentagePanierTransformerEnCommande') 
        ->setParameter('subQuery', $subQuery) 
        ->join("basket.status", "status")
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->andWhere("status.name = 'Valider'")
        ->getQuery()
        ->getOneOrNullResult();

        $query['PourcentagePanierTransformerEnCommande'] = round($query['PourcentagePanierTransformerEnCommande'], 2);
        return $query;
    }

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


    public function recurrenceOrderCustomerAction(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {
        
        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $subQuery = $this->createQueryBuilder('basket')
        ->select('COUNT(customer) AS NbNewCustomer')  
        ->join('basket.customer', 'customer')
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->andWhere("status.name = 'Valider'")
        ->having('')
        ->getQuery()
        ->getOneOrNullResult();

        $query = $this->createQueryBuilder('basket')
        ->select('((COUNT(basket) / :subQuery) * 100) AS PourcentagePanierTransformerEnCommande') 
        ->setParameter('subQuery', $subQuery) 
        ->join("basket.status", "status")
        ->where('basket.dateCreated BETWEEN :beginDate AND :endDate')
        ->setParameter('beginDate', $beginDate)
        ->setParameter('endDate', $endDate)
        ->andWhere("status.name = 'Valider'")
        ->getQuery()
        ->getOneOrNullResult();

        return $query;

    }
    // SELECT COUNT(*)

    // FROM command

    // JOIN user
    // ON user.id = command.user_id

    // WHERE user.id IN (
    // -- une seule et unique commande
    //     SELECT command.user_id
    //     FROM command
    //     WHERE command.status IN (200, 300)
    //     GROUP BY command.user_id
    //     HAVING COUNT(*) = 1
    // )
    // AND user.id IN (
    // -- une commande sur la periode donnee
    //     SELECT command.user_id
    //     FROM command
    //     WHERE command.status IN (200, 300)
    //     AND command.created_at BETWEEN '2022-05-01' AND '2022-07-11'  
    //     GROUP BY command.user_id
    //     HAVING COUNT(*) = 1    

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
