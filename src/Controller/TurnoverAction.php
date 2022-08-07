<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


class TurnoverAction extends AbstractController
{

    public function __construct(
        private OrderRepository $orderRepository
    )
    {
    }


    public function __invoke(): JsonResponse
    {
        $query = $this->orderRepository->turnover();
        dump($query);
        return new JsonResponse($query);
    }
 
}



?>