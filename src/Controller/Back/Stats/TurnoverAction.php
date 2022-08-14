<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use ProxyManager\Factory\RemoteObject\Adapter\JsonRpc;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class TurnoverAction extends AbstractController
{

    public function __construct(
        private BasketRepository $basketRepository
    )
    {
    }


    public function __invoke(): JsonResponse
    {
        $query = $this->basketRepository->turnover();
        return new JsonResponse($query);
    }
 
}



?>