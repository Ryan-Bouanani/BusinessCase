<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class OrderConversionPercentageAction extends AbstractController
{

    public function __construct(
        private BasketRepository $basketRepository
    )
    {
    }


    public function __invoke(): JsonResponse
    {
        $nbBasketAndOrders = $this->basketRepository->nbBasketAndOrders();
        $nbOrder = $this->basketRepository->nbOrder();

        $query['PercentageBasketsConvertedIntoOrders'] = ROUND((($nbOrder['NbOrder'] / $nbBasketAndOrders['NbBasketAndOrders']  ) * 100), 2);

        return new JsonResponse($query);
    }
}



?>