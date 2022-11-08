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
        $NbBasketAndOrders = $this->basketRepository->nbBasketAndOrders();

        $query['PercentageAbandonedBaskets'] = ROUND((($nbAbandonedBasket['NbAbandonedBasket'] / $NbBasketAndOrders['NbBasketAndOrders']) * 100), 2);

        return new JsonResponse($query);
    }
}



?>