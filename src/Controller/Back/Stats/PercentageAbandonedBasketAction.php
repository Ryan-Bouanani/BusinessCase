<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class PercentageAbandonedBasketAction extends BaseControllerStats
{

    public function __construct(
        BasketRepository $basketRepository,
        RequestStack $requestStack,
    )
    {
        $this->basketRepository = $basketRepository;
        $this->requestStack = $requestStack;
    }


    public function __invoke(): JsonResponse
    {
        $nbAbandonedBasket = $this->getQuery('abandonedBasket', $this->basketRepository, $this->requestStack);
        $NbBasketAndOrders = $this->getQuery('nbBasketAndOrders', $this->basketRepository, $this->requestStack);

        if ( $nbAbandonedBasket['NbAbandonedBasket'] === 0) {
            $query['PercentageAbandonedBaskets'] = 0;
        } else {
            $query['PercentageAbandonedBaskets'] = ROUND((($nbAbandonedBasket['NbAbandonedBasket'] / $NbBasketAndOrders['NbBasketAndOrders']) * 100), 2);
        }

        return new JsonResponse($query);
    }
}



?>