<?php

namespace App\Controller\Back\Stats;

use App\Controller\Back\Stats\BaseControllerStats;
use App\Repository\BasketRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class NbOrderAction extends BaseControllerStats {

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
        $query = $this->getQuery('nbOrder', $this->basketRepository, $this->requestStack);
        return new JsonResponse($query);
    }
}

?>