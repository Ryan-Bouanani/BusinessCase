<?php

namespace App\Controller;

use App\Repository\CustomerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class NbNewCustomer extends AbstractController
{

    public function __construct(
        private CustomerRepository $customerRepository
    )
    {}



    public function __invoke(): JsonResponse
    {
        $query = $this->customerRepository->NbNewCustomer();
        return new JsonResponse($query);
    }
}

?>