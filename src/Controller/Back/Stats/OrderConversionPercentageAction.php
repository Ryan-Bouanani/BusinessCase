<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class OrderConversionPercentageAction extends BaseControllerStats
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
        $nbOrder = $this->getQuery('nbOrder', $this->basketRepository, $this->requestStack);
        $nbBasketAndOrders = $this->getQuery('nbBasketAndOrders', $this->basketRepository, $this->requestStack);

        if ($nbOrder['NbOrder'] === 0) {
            $query['PercentageBasketsConvertedIntoOrders'] = 0;
        } else {
            $query['PercentageBasketsConvertedIntoOrders'] = ROUND((($nbOrder['NbOrder'] / $nbBasketAndOrders['NbBasketAndOrders']  ) * 100), 2);
        }

        return new JsonResponse($query);
    }
}



?>