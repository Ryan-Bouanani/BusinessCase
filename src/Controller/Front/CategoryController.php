<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/{id}', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('front/category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }
}
