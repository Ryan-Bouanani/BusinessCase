<?php

namespace App\Controller\Front;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/{id}', name: 'app_detail_product')]
    public function index(
        ProductRepository $productRepository, 
        int $id
        ): Response
    {

        $product = $productRepository->getProductInfo($id);
    
        $productSamecategory = $productRepository->getProductSameCategory($product[0]->getCategory()->getId(), 6);
        // $samemark = $productRepository->findSameMark($product[0][0]->getMark()->getId());

        // $comments = $commentRepository->findAllCommentWithUser($product[0][0]);


        return $this->render('front/product/index.html.twig', [
            'product' => $product,
            'productSamecategory' => $productSamecategory,
        ]);
    }
}
