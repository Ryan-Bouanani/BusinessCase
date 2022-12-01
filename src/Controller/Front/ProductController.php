<?php

namespace App\Controller\Front;

use App\Entity\Product;
use App\Entity\Review;
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
    #[Route('/{id}', name: 'app_detail_product')]
    public function index(
        ProductRepository $productRepository, 
        int $id,
        ReviewRepository $reviewRepository,
        Request $request,
        ): Response
    {

        $product = $productRepository->getProductInfo($id);
    
        $productSamecategory = $productRepository->getProductSameCategory($product[0]->getCategory()->getId(), 6);
        // $samemark = $productRepository->findSameMark($product[0][0]->getMark()->getId());

        $reviews = $reviewRepository->getAllReviewProduct($id, $request->query->getInt('page', 1));

        $firstReview = true;
        $customer = $this->getUser();
        // Si utilisateur connécté il peut alors donner un avis au produit
        if ($customer) {
            foreach ($reviews as $review) {
                if ($review->getCustomer() === $customer) {
                    $firstReview = false;
                }
            }
            if ($firstReview) {
                $review = new Review();
                $review->setProduct($product[0]);
                $review->setCustomer($customer);
                
                 // Creation du formulaire d'avis de produit
                $form = $this->createForm(ReviewType::class, $review);

                // On inspecte les requettes du formulaire
                $form->handleRequest($request);

                // Si le formulaire est envoyé et valide
                if ($form->isSubmitted() && $form->isValid()) {
                            
                    // On met l'utilisateur à jour en bdd
                    $reviewRepository->add($review, true);       
                    return $this->redirectToRoute('app_detail_product', [
                        'id' => $product[0]->getId(),
                    ]);
                } else {
                    $this->addFlash(
                        'error',
                        'Veuillez sélectionner un moyen de paiement'
                    );
                }
                return $this->render('front/product/index.html.twig', [
                    'product' => $product,
                    'productSamecategory' => $productSamecategory,
                    'reviews' => $reviews,
                    'formReview' => $form->createView(),
                ]);
            } else {
                return $this->render('front/product/index.html.twig', [
                    'product' => $product,
                    'productSamecategory' => $productSamecategory,
                    'reviews' => $reviews,
                ]);
            }
        }

    }
}
