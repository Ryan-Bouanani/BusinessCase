<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // Récupere les derniers produits ajoutés
    public function getNewProduct(): array {

        $query =  $this->createQueryBuilder('product')
            ->select('product, image, AVG(review.note) AS Note, COUNT(review) AS Avis')
            ->join('product.images', 'image')
            ->leftJoin('product.reviews', 'review')
            // ->where('product.dateAdded >= DATE(NOW()) - INTERVAL 30 DAY')
            ->where('image.isMain = true')
            ->andWhere('product.active = 1')
            ->add('groupBy', ':orderBy')
            ->groupBy('product')
            ->orderBy('product.dateAdded', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
        return $query;
    }

    // Récupere les produits les mieux notés
    public function getTopRatedproduct(): array {


        $query =  $this->createQueryBuilder('product')
            ->select('product, image, AVG(review.note) AS Note, COUNT(review) AS Avis')
            ->join('product.images', 'image')
            ->leftJoin('product.reviews', 'review')
            ->where('image.isMain = true')
            ->andWhere('product.active = 1')
            ->groupBy('product')
            ->orderBy('Note', 'DESC')
            ->addOrderBy('Avis', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
        return $query;
    }

    // Récupère la produit correspondant au bon id
    public function getProductInfo(int $id): array {
        return $this->createQueryBuilder('product')
            ->select('product, AVG(review.note) AS Note, COUNT(DISTINCT (review)) AS Avis')
            ->leftJoin('product.reviews', 'review')
            ->where('product.id = :id')
            ->groupBy('product')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        ;
    }
    // Récupère les produits de la même catégorie
    public function getProductSameCategory(int $id): array {
        return $this->createQueryBuilder('product')
            ->select('product, image, AVG(review.note) AS Note, COUNT(review) AS Avis')
            ->join('product.reviews', 'review')
            ->join('product.images', 'image')
            ->join('product.category', 'category')
            ->where('category.id = :id')
            ->andWhere('image.isMain = true')
            ->setParameter('id', $id)
            ->setMaxResults(6)
            ->getQuery()
            ->getResult()
        ;
    }

        // Récupère la produit correspondant au bon id
        public function getProductShoppingCart(int $id): Product {
            return $this->createQueryBuilder('product')
                ->select('product', 'image')
                ->leftjoin('product.images', 'image')
                ->where('product.id = :id')
                ->andWhere('image.isMain = true')
                ->setParameter('id', $id)
                ->getQuery()
                ->getOneOrNullResult();
            ;
        }
    
    // Récupere tout les produits avec leurs image principale
    public function getQbAll(): QueryBuilder {
        $qb = parent::getQbAll();
        return $qb->select('product', 'image')
        ->join('product.images', 'image')
        ->where('image.isMain = true')
        ->orderBy('product.id', 'ASC')
        ;
    }

    // Récupère les produits correspondant à la recherche 
    public function getProductBySearch(string $searchValue): array {
        return $this->createQueryBuilder('p')
            ->select('p', 'image')
            ->join('p.images', 'image')
            ->join('p.brand', 'b')
            ->where('p.title LIKE :searchValue OR b.label LIKE :searchValue')
            ->andWhere('image.isMain = true')
            ->andWhere('p.active = 1')
            ->setParameter('searchValue', '%'.$searchValue.'%')
            ->getQuery()
            ->getResult()
        ;
    }

//    /**
//     * @return Product[] Returns an array of Product objects
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

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
