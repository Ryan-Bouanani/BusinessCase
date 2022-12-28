<?php

namespace App\Repository;

use App\Entity\Customer;
use Doctrine\ORM\Query;
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


    public function NbNewCustomer(?\DateTime $startDate = null, ?\DateTime $endDate = null): array {

        $queryModifier = $this->createQueryBuilder('customer')
            ->select('COUNT(customer) AS NbNewCustomer')
        ;

        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getOneOrNullResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $NbNewCustomer = $this->getDateFilteredResult($queryModifier, $resultModifier, 'registrationDate', $startDate, $endDate);
        
        // Check if result is null
        if (empty($NbNewCustomer['NbNewCustomer'])) {
            $NbNewCustomer['NbNewCustomer'] = 0;
        }
        return $NbNewCustomer;
    }

    public function NbNewCustomerArray(?\DateTime $startDate = null, ?\DateTime $endDate = null): array {

        // If startDate is not provided, set it to the earliest newCustomer registration date in the database
        if (!$startDate) {
            $startDate = $this->createQueryBuilder('customer')
                ->select('MIN(customer.registrationDate)')
                ->getQuery()
                ->getSingleScalarResult();
            $startDate = new \DateTime($startDate);
        }
        // If endDate is not provided, set it to the current date and time
        if (!$endDate) {
            $endDate = new \DateTime('NOW');
        }

        $queryModifier = $this->createQueryBuilder('customer')
            ->select('COUNT(customer) AS NbNewCustomer')
        ;
        // Calculate the time difference between start date and end date
        $diff = $startDate->diff($endDate);
        //   return [$diff, $startDate, $endDate, $diff->y >= 4];
        if ($diff->days <= 30) {
            // If time between start date and end date is less than or equal to 1 month, group by day
            $queryModifier->addSelect('SUBSTRING(customer.registrationDate, 1, 10) as RegistrationDate');
        } 
        // elseif ($diff->days <= 120) {
        //         // If time between start date and end date is more than 4 months, group by week
        //         $queryModifier->addSelect("WEEK(customer.registrationDate) as RegistrationDate");
        // } 
        elseif ($diff->y >= 4) {
                // If time between start date and end date is more than 4 months, group by week
                $queryModifier->addSelect("YEAR(customer.registrationDate) as RegistrationDate");
        } 
        else {
            // If time between start date and end date is more than 4 years, group by year
            $queryModifier->addSelect('SUBSTRING(customer.registrationDate, 1, 7) as RegistrationDate');
        }
       
        $queryModifier ->groupBy("RegistrationDate")
        ->orderBy("RegistrationDate", "ASC");


        $resultModifier = function(Query $query) {
            // Return the desired result
            return $query->getResult();
        };
        // Call the getDateFilteredResult method for add filter date is in query
        $NbNewCustomer = $this->getDateFilteredResult($queryModifier, $resultModifier, 'registrationDate', $startDate, $endDate);
    
        return $NbNewCustomer;
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
