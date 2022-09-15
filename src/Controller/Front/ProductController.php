<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/product', name: 'app_detail_product')]
    public function index(): Response
    {
        return $this->render('front/product/index.html.twig', [
            'controller_name' => 'ProductController',
        ]);
    }
}
