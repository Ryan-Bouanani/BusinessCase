<?php

namespace App\Controller\Back\Stats;

use App\Repository\BasketRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class BestSellingProductAction extends BaseControllerStats
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
        $query = $this->getQuery('bestSellingProduct', $this->basketRepository, $this->requestStack);
        return new JsonResponse($query);
    }
}

?>