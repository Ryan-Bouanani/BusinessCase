<?php

namespace App\Controller;

use App\Repository\BasketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class NbBasketAction extends AbstractController
{

    public function __construct(
        private BasketRepository $basketRepository
    )
    {}



    public function __invoke(): int
    {
        return $this->basketRepository->count([]);

    }
}

?>