<?php

namespace App\Controller;

use App\Repository\BasketRepository;
use App\Repository\ContentShoppingCartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;


class AveragePriceBasketAction extends AbstractController
{

    public function __construct(
        private BasketRepository $basketRepository
    )
    {
    }


    public function __invoke(): JsonResponse
    {
        $query = $this->basketRepository->averagePriceBasket();

        return new JsonResponse($query);

    }
}



?>