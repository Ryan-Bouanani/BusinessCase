<?php
namespace App\Controller\Back\Stats;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;

abstract class BaseControllerStats extends AbstractController
{
    protected function getQuery(string $method, object $repository, RequestStack $requestStack): array 
    {
        $request = $requestStack->getCurrentRequest();
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $startDate = $startDate ? new \DateTime($startDate) : null;
        $endDate = $endDate ? new \DateTime($endDate) : null;

        return $repository->$method($startDate, $endDate);
    }
}
?>