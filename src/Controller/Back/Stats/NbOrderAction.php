<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class NbOrderAction extends AbstractController
{

    public function __construct(
        private BasketRepository $basketRepository
    )
    {}



    public function __invoke(): JsonResponse
    {
        $query = $this->basketRepository->nbOrder();
        return new JsonResponse($query);
    }
}

?>