<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


class TurnoverAction extends AbstractController
{

    public function __construct(
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }


    public function __invoke(): JsonResponse
    {
        $query = $this->entityManager->createQuery(
            'SELECT SUM(c.price * c.quantity)
            FROM App\Entity\ContentShoppingCart c
            JOIN App\Entity\Order o
            WHERE o.basket = c.basket'

        );
        $products = $query->getSingleScalarResult();
        
        // return response
        return new JsonResponse($products);
    }
 
}



?>