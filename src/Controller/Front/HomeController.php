<?php

namespace App\Controller\Front;

use App\Entity\Visit;
use App\Repository\BasketRepository;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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


    /**
     * Ce controller va servir à afficher la page d'accueil
     *
     * @return Response
     */
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Récupère les nouveaux produits
        $newProducts = $this->productRepository->getNewProduct();
        // Récupère les produits les mieux notés
        $topRatedProducts = $this->productRepository->getTopRatedProduct();
        // Récupère les marques
        $brands = $this->brandRepository->getBrand();
        // Récupère les derniers avis
        $reviews = $this->reviewRepository->getReview();

        // Ajouter une ligne date dans la table nbVisites
            $visit = new Visit();
            $visit->setVisitAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
            $this->entityManager->persist($visit);
            $this->entityManager->flush();
        // 

        return $this->render('front/home/index.html.twig', [
            'newProducts' => $newProducts,
            'topRatedProducts' => $topRatedProducts,
            'brands' => $brands,
            'reviews' => $reviews,
        ]);
    }

    /**
     * Ce controller va servir à renvoyer les produits correspondants à la recherche de l'utilisateur (barre de recherche back office)
     *
     * @param string $searchValue
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/filterSearch/{searchValue}/{isSearchBack}', name: 'app_basket_productFilterSearch', methods: ['GET'])]
    public function getProductByFilter(
        string $searchValue, 
        ProductRepository $productRepository,
        bool $isSearchBack = false,
        ): Response
    {
        $searchValue = json_decode($searchValue, true);
        
        // On récupère tout les produits pour l'input d'ajout de produits
        $products = $productRepository->getProductBySearch($searchValue);
        
        // On répond en JSON
        // Sinon aucun produit trouvé, on affiche le message suivant
        if (count($products) === 0) {
            return new JsonResponse(['error' => 'Aucun produits ne corresponds à votre recherche']);
        } else {
            // Sinon, on affiche les produits trouvés
            return new JsonResponse($this->renderView('shared/partials/_searchResult.html.twig', [
                    'products' => $products,
                    'isSearchBack' => $isSearchBack,
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
