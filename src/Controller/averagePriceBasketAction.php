<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


class averagePriceBasketAction extends AbstractController
{

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }


    public function __invoke(): JsonResponse
    {
        $subQuery = $this->entityManager->createQuery(
            'SELECT COUNT(b.id) FROM App\Entity\Basket b'
        );
        $resultSubQuery = $subQuery->getSingleScalarResult();
        $query = $this->entityManager->createQuery(
            'SELECT SUM(c.price * c.quantity) / ('. $resultSubQuery .')
            FROM App\Entity\ContentShoppingCart c'
        );
        $products = $query->getSingleScalarResult();
        
        // return response
        return new JsonResponse($products);
    }
}



?>