<?php

namespace App\Controller\Back\Stats;

use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;

class NbNewCustomerAction extends BaseControllerStats
{
    public function __construct(
        CustomerRepository $customerRepository,
        RequestStack $requestStack,
    )
    {
        $this->customerRepository = $customerRepository;
        $this->requestStack = $requestStack;
    }

    public function __invoke(): JsonResponse
    {
        $query = $this->getQuery('NbNewCustomerArray', $this->customerRepository, $this->requestStack);
        return new JsonResponse($query);
    }
}

?>