<?php

namespace App\Controller\Front;

use App\Entity\NbVisite;
use App\Form\Filter\ProductSearchFilterType;
use App\Repository\BasketRepository;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use App\Service\ShoppingCart\ShoppingCartService;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    public function __construct(
        private ProductRepository $productRepository,
        private BasketRepository $basketRepository,
        private CategoryRepository $categoryRepository,
        private BrandRepository $brandRepository,
        private ReviewRepository $reviewRepository,
        private EntityManagerInterface $entityManager,
    ) { }

    #[Route('/', name: 'app_home')]
    public function index(
        SessionInterface $session,
    ): Response
    {
        // Récupere les nouveaux produits
        $newProducts = $this->productRepository->getNewProduct();
        // Récupere les produits les mieux notés
        $topRatedProducts = $this->productRepository->getTopRatedproduct();
        // Récupère les marques
        $brandts = $this->brandRepository->getBrand();
        // Récupère les derniers avis
        $reviews = $this->reviewRepository->getReview();

        // Ajouter une ligne date dans la table nbVisites
            $nbVisite = new NbVisite();
            $nbVisite->setVisitAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $this->entityManager->persist($nbVisite);
            $this->entityManager->flush();
        // 

        return $this->render('front/home/index.html.twig', [
            'newProducts' => $newProducts,
            'topRatedProducts' => $topRatedProducts,
            'brandts' => $brandts,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Ce controller va servir à renvoyer les produits corréspondants à la recherche de l'utilisateur (barre de recherche back office)
     *
     * @param string $searchValue
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/filterSearch/{searchValue}', name: 'app_basket_productFilterSearch', methods: ['GET'])]
    public function getProductByFilter(
        string $searchValue, 
        ProductRepository $productRepository,
        ): Response
    {
        $searchValue = json_decode($searchValue, true);
        
        // On récupere tout les produits pour l'input d'ajout de produits
        $products = $productRepository->getProductBySearch($searchValue);
        
        // On répond en JSON
        // Sinon aucun produit trouvé, on affiche le message suivant
        if (count($products) === 0) {
            return new JsonResponse(['error' => 'Aucun produits ne corresponds à votre recherche']);
        } else {
            // Sinon, on affiche les produits trouvés
            return new JsonResponse($this->renderView('back/partials/_searchResult.html.twig', [
                    'products' => $products,
            ]));
        }  
    }

    /**
     * Ce controller va servir à renvoyer les produits corréspondants à la recherche de l'utilisateur (barre de recherche)
     *
     * @param string $searchValue
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/filterSearchFront/{searchValue}', name: 'app_productFilterSearchFront', methods: ['GET'])]
    public function getProductBySearchFront(
        string $searchValue, 
        ProductRepository $productRepository,
        ): Response
    {
        $searchValue = json_decode($searchValue, true);
        
        // On récupere tout les produits pour l'input d'ajout de produits
        $products = $productRepository->getProductBySearch($searchValue);
        
        // On répond en JSON
        // Sinon aucun produit trouvé, on affiche le message suivant
        if (count($products) === 0) {
            // Sérialisation de php à json pour pouvoir le désérialiser plus tard pour pouvoir ensuite l'utiliser en js
            return new JsonResponse(['error' => 'Aucun produits ne corresponds à votre recherche']);
        } else {
            // Sinon, on affiche les produits trouvés
            return new JsonResponse($this->renderView('/front/partials/_searchResult.html.twig', [
                    'products' => $products,
            ]));
        }  
    }

    
    #[Route('/legal_notice', name: 'app_home_legal_notice')]
    public function legal_notice(): Response
    {
        return $this->render('front/home/legal_notice.html.twig', [
        ]);
    }
    #[Route('terms_of_sales', name: 'app_home_terms_of_sales')]
    public function terms_of_sales(): Response
    {
        return $this->render('front/home/terms_of_sales.html.twig', [
        ]);
    }
    #[Route('/privacy_policies', name: 'app_home_privacy_policies')]
    public function privacy_policies(): Response
    {
        return $this->render('front/home/privacy_policies.html.twig', [
        ]);
    }
}
