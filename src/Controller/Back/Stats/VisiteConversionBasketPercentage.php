<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use App\Repository\VisitRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class VisiteConversionBasketPercentage extends BaseControllerStats
{

    public function __construct(
        VisitRepository $visitRepository,
        BasketRepository $basketRepository,
        RequestStack $requestStack,
    )
    {
        $this->visitRepository = $visitRepository;
        $this->basketRepository = $basketRepository;
        $this->requestStack = $requestStack;
    }


    public function __invoke(): JsonResponse
    {
        $nbVisit = $this->getQuery('nbVisit', $this->visitRepository, $this->requestStack);
        $nbBasketAndOrders = $this->getQuery('nbBasketAndOrders', $this->basketRepository, $this->requestStack);

        if ($nbVisit['NbVisit'] === 0 ) {
            $query['PercentageVisitsConvertedIntoBasket'] = 0;
        } else {         
            $query['PercentageVisitsConvertedIntoBasket'] = round((($nbBasketAndOrders['NbBasketAndOrders'] / $nbVisit['NbVisit']) * 100), 2);
        }
        
        return new JsonResponse($query);
    }
}

?>