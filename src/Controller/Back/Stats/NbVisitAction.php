<?php

namespace App\Controller\Back\Stats;

use App\Repository\VisitRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class NbVisitAction extends BaseControllerStats
{
    public function __construct(
        VisitRepository $visitRepository,
        RequestStack $requestStack,
    )
    {
        $this->visitRepository = $visitRepository;
        $this->requestStack = $requestStack;
    }

    public function __invoke(): JsonResponse
    {
        $query = $this->getQuery('nbVisitArray', $this->visitRepository, $this->requestStack);
        return new JsonResponse($query);
    }
}

?>