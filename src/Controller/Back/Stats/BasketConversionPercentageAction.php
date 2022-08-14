<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use App\Repository\NbVisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BasketConversionPercentageAction extends AbstractController
{

    public function __construct(
        private NbVisiteRepository $visiteRepository,
        private BasketRepository $basketRepository
    )
    {}



    public function __invoke(): JsonResponse
    {
        $nbVisit = $this->visiteRepository->nbVisit();
        $nbBasket = $this->basketRepository->nbBasket();

        $query['PourcentageConversionPanier'] = round((($nbBasket['NbBasket'] / $nbVisit['NbVisite']) * 100), 2);
        
        return new JsonResponse($query);
    }
}

?>