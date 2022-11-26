<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use App\Repository\NbVisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class VisiteConversionBasketPercentage extends AbstractController
{

    public function __construct(
        private NbVisiteRepository $visiteRepository,
        private BasketRepository $basketRepository
    )
    {}



    public function __invoke(): JsonResponse
    {
        $nbVisit = $this->visiteRepository->nbVisit();
        $nbBasketAndOrders = $this->basketRepository->nbBasketAndOrders();

        $query['PercentageVisitsConvertedIntoBasket'] = round((($nbBasketAndOrders['NbBasketAndOrders'] / $nbVisit['NbVisit']) * 100), 2);
        
        return new JsonResponse($query);
    }
}

?>