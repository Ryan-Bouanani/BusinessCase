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
        $nbBasket = $this->basketRepository->nbBasket();
        $nbOrder = $this->basketRepository->nbOrder();

        $query['PourcentagePanierTransformerEnCommande'] = ROUND((($nbOrder['NbOrder'] / $nbBasket['NbBasket']  ) * 100), 2);

        return new JsonResponse($query);
    }
}



?>