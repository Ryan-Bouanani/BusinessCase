<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class PercentageAbandonedBasketAction extends AbstractController
{

    public function __construct(
        private BasketRepository $basketRepository
    )
    {
    }


    public function __invoke(): JsonResponse
    {
        $nbAbandonedBasket = $this->basketRepository->abandonedBasket();
        $nbBasket = $this->basketRepository->nbBasket();

        $query['PercentageAbandonedBaskets'] = ROUND((($nbAbandonedBasket['NbAbandonedBasket'] / $nbBasket['NbBasket']) * 100), 2);

        return new JsonResponse($query);
    }
}



?>