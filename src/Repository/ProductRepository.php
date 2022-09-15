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

    public function getNewAndTopRatedProduct(string $orderBy = null): array {
dump($orderBy);

        $query =  $this->createQueryBuilder('product')
            ->select('product, image, AVG(review.nbStar) AS note, COUNT(review)')
            ->join('product.brand', 'brand')
            ->join('product.category', 'category')
            ->leftJoin('product.promotion', 'promotion')
            ->join('product.images', 'image')
            ->join('product.reviews', 'review')
            // ->leftJoin('product.promotion', 'promotion')
            // ->where('product.dateAdded >= DATE(NOW()) - INTERVAL 30 DAY')
            ->where('image.isMain = true')
            ->andWhere('product.active = 1')
            ->add('groupBy', ':orderBy')
            ->groupBy('product')
            // ->orderBy(':orderBy', 'DESC')
            ->setParameter('orderBy', $orderBy)
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
        return $query;
    }
    // public function getTopRatedproduct(): array {


    //     $query =  $this->createQueryBuilder('product')
    //         ->select('product, image, AVG(review.nbStar) AS note, COUNT(review)')
    //         ->join('product.brand', 'brand')
    //         ->join('product.category', 'category')
    //         ->leftJoin('product.promotion', 'promotion')
    //         ->join('product.images', 'image')
    //         ->join('product.reviews', 'review')
    //         ->where('image.isMain = true')
    //         ->andWhere('product.active = 1')
    //         ->groupBy('product')
    //         ->orderBy('note', 'DESC')
    //         ->setMaxResults(10)
    //         ->getQuery()
    //         ->getResult()
    //     ;
    //     return $query;
    // }



    

    public function getQbAll(): QueryBuilder {
        $qb = parent::getQbAll();
        return $qb->select('product')
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
