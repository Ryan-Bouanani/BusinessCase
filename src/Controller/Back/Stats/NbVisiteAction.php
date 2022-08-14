<?php

namespace App\Controller\Back\Stats;

use App\Repository\NbVisiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class NbVisiteAction extends AbstractController
{

    public function __construct(
        private NbVisiteRepository $visiteRepository
    )
    {}



    public function __invoke(): JsonResponse
    {
        $query = $this->visiteRepository->nbVisit();
        return new JsonResponse($query);
    }
}

?>