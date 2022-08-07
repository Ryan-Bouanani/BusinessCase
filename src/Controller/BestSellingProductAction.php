<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BestSellingProductAction extends AbstractController
{

    public function __construct(
        private OrderRepository $orderRepository
    )
    {}



    public function __invoke(): JsonResponse
    {
        $query = $this->orderRepository->bestSellingProduct();
        return new JsonResponse($query);
    }
}

?>