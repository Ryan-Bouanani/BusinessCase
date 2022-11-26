<?php

namespace App\Repository;

use App\Entity\Customer;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Customer>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomerRepository extends AbstractRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customer::class);
    }

    public function add(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Customer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Customer) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    // Récupere tout les produits avec leurs image principale
    public function getQbAll(): QueryBuilder {
        $qb = parent::getQbAll();
        return $qb->select('customer')
            ->join('customer.gender', 'gender')
        ;
    }


    public function NbNewCustomer(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $query = $this->createQueryBuilder('customer')
            ->select('COUNT(customer) AS NbNewCustomer')
            ->where('customer.registrationDate BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        return $query;
    }

    public function NbNewCustomerArray(?\DateTime $beginDate = null, ?\DateTime $endDate = null): array {

        if ($beginDate === null || $endDate === null) {
            $beginDate = new DateTime('2018-01-01');
            $endDate = new DateTime('now');
        }

        $query = $this->createQueryBuilder('customer')
            ->select('COUNT(customer) AS NbNewCustomer','customer.registrationDate AS RegistrationDate', 'SUBSTRING(customer.registrationDate, 1, 7) as Date')
            ->where('customer.registrationDate BETWEEN :beginDate AND :endDate')
            ->setParameter('beginDate', $beginDate)
            ->setParameter('endDate', $endDate)
            ->groupBy("Date")
            ->orderBy("RegistrationDate", "ASC")
            ->getQuery()
            ->getResult()
        ;
        return $query;
    }

//    /**
//     * @return Customer[] Returns an array of Customer objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Customer
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
