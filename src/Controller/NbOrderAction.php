<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NbOrderAction extends AbstractController
{

    public function __construct(
        private OrderRepository $orderRepository
    )
    {}



    public function __invoke(): int
    {
        return $this->orderRepository->count([]);

    }
}

?>