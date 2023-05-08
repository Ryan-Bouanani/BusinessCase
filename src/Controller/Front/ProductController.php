<?php

namespace App\Controller\Front;

use App\Entity\Review;
use App\Form\AddToBasketType;
use App\Form\ReviewType;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/product')]
class ProductController extends AbstractController
{
    /**
     * Ce controller va servir à afficher la page détail de chaque produit
     *
     * @param ProductRepository $productRepository
     * @param string $slug
     * @param ReviewRepository $reviewRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/{slug}', name: 'app_detail_product')]
    public function productDetail(
        ProductRepository $productRepository, 
        string $slug,
        ReviewRepository $reviewRepository,
        Request $request,
        ): Response
    {

        // On récupère les info précises du produit
        $product = $productRepository->getProductInfo($slug);
        if (!$product[0]->isActive()) {
            return $this->redirectToRoute('app_home');
        }
    
        // On récupère les produits de la même category et de la même marque
        $productSameCategory = $productRepository->getProductSameCategory($product[0]->getCategory()->getId(), 6);
        $productSameBrand = $productRepository->getProductByBrand($product[0]->getBrand()->getId(), $product[0], 10);

        $reviews = $reviewRepository->getAllReviewProduct($slug, $request->query->getInt('page', 1));
        // Si utilisateur connecté il peut alors donner un avis au produit
        $firstReview = true;
        $customer = $this->getUser();

        $formAddToBasket = $this->createForm(AddToBasketType::class);
        $firstReview = $customer ? Review::isFirstReview($customer, $reviews) : false;

        // Si c'est le premier avis de l'utilisateur 
        if ($firstReview) {
            $review = new Review();
            $review->setProduct($product[0]);
            $review->setCustomer($customer);
            
                // Creation du formulaire d'avis de produit
            $formReview = $this->createForm(ReviewType::class, $review);

            // On inspecte les requêtes du formulaire
            $formReview->handleRequest($request);

            // Si le formulaire est envoyé et valide
            if ($formReview->isSubmitted() && $formReview->isValid()) {
                        
                // On met l'utilisateur à jour en bdd
                $reviewRepository->add($review, true);       
                return $this->redirectToRoute('app_detail_product', [
                    'slug' => $product[0]->getSlug(),
                ]);
            }
            return $this->render('front/product/index.html.twig', [
                'product' => $product,
                'productSameCategory' => $productSameCategory,
                'reviews' => $reviews,
                'formReview' => $formReview->createView(),
                'formAddToBasket' => $formAddToBasket->createView(),
                'productSameBrand' => $productSameBrand,
            ]);
        }  
        return $this->render('front/product/index.html.twig', [
            'product' => $product,
            'productSameCategory' => $productSameCategory,
            'reviews' => $reviews,
            'formAddToBasket' => $formAddToBasket->createView(),
            'productSameBrand' => $productSameBrand,
        ]);

    }
}
