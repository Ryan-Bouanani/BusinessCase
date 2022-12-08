<?php

namespace App\Controller\Front;

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
    /**
     * Ce controller va servir à afficher la page de chaque produit
     *
     * @param ProductRepository $productRepository
     * @param integer $id
     * @param ReviewRepository $reviewRepository
     * @param Request $request
     * @return Response
     */
    #[Route('/{id}', name: 'app_detail_product')]
    public function index(
        ProductRepository $productRepository, 
        int $id,
        ReviewRepository $reviewRepository,
        Request $request,
        ): Response
    {

        // On récupere les info précises du produit
        $product = $productRepository->getProductInfo($id);
        if (!$product[0]->isActive()) {
            return $this->redirectToRoute('app_home');
        }
    
        // On récupere les produits de la même catégory
        $productSamecategory = $productRepository->getProductSameCategory($product[0]->getCategory()->getId(), 6);

        // $samemark = $productRepository->findSameMark($product[0][0]->getMark()->getId());

        $reviews = $reviewRepository->getAllReviewProduct($id, $request->query->getInt('page', 1));

        // Si utilisateur connécté il peut alors donner un avis au produit
        $firstReview = true;
        $customer = $this->getUser();
        if ($customer) {
            // On vérifie si c'est son premier avis sur le produit
            foreach ($reviews as $review) {
                if ($review->getCustomer() === $customer) {
                    $firstReview = false;
                }
            }
            // Si c'est le premier avis de l'utilisateur 
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
            } 
        } 
        return $this->render('front/product/index.html.twig', [
            'product' => $product,
            'productSamecategory' => $productSamecategory,
            'reviews' => $reviews,
        ]);

    }
}
