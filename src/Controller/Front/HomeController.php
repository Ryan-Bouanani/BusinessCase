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
        FilterBuilderUpdaterInterface $builderUpdater,
        Request $request,
        SessionInterface $session,
        ShoppingCartService $shoppingCartService,
        BasketRepository $basketRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {
        $customer = $this->getUser();

        // si je suis connecter
        if ($customer) {
            $shoppingCart = $session->get('shoppingCart', []);
            if ($shoppingCart) {
                if (!$shoppingCart->getCustomer()) {
                    $shoppingCart->setCustomer($customer);
                }
            }

            // // on va recuérer mon dernier panier
            // $oldShoppingCart = $basketRepository->findBasketWithCustomer($customer->getId());

            // $shoppingCart = $session->get('shoppingCart', []);
            // if ($shoppingCart) {
            //     $shoppingCart = $this->basketRepository->find($shoppingCart->getId());
            // }

            // // si j'avais un panier la derniere fois que je me suis connecté on le set en tant que panier actuelle
            // if (!empty($oldShoppingCart) && $oldShoppingCart[0] !== $shoppingCart) {

            //     // si j'ai déja un panier je le supprime

            //     if ($shoppingCart) {
            //         $shoppingCart = $this->basketRepository->find($shoppingCart->getId());
            //         $basketRepository->remove($shoppingCart);
            //         $entityManager->flush();
            //     }

            //     // sinon on met l'ancien panier en principale
            //     // $shoppingCart = $oldShoppingCart[0];
            //     // $session->remove('basket');
            //     // $session->remove('shoppingCart');
            //     // $session->remove('QTY');
            //     // dd($session);
            //     // dd($shoppingCart);

            //     $shoppingCartService->transformShoppingCartToBasketSesion();
            // }
        } 
        // dd($session->get('shoppingCart', []), $session->get('basket', []));

        $qb = $this->productRepository->getQbAll();

        // Barre de recherche de produits
        $filterForm = $this->createForm(
            ProductSearchFilterType::class,
            null,
            ['method' => 'GET']
        );

        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->get($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

        // Récupere les nouveaux produits
        $newProducts = $this->productRepository->getNewProduct();

        // Récupere les produits les mieux notés
        $topRatedProducts = $this->productRepository->getTopRatedproduct();

        // Récupère les marques
        $brandts = $this->brandRepository->getBrand();

        // Récupère les derniers avis
        $reviews = $this->reviewRepository->getReview();

;

        // Ajouter une ligne date dans la table nbVisites
        $nbVisite = new NbVisite();
        $nbVisite->setVisitAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));
        $this->entityManager->persist($nbVisite);
        $this->entityManager->flush();

        return $this->render('front/home/index.html.twig', [
            'filterSearchForm' => $filterForm->createView(),
            'newProducts' => $newProducts,
            'topRatedProducts' => $topRatedProducts,
            'brandts' => $brandts,
            'reviews' => $reviews,
        ]);
    }

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
            return new JsonResponse(['error' => 'Aucun produits ne corresponds à votre recherche']);
        } else {
            // Sinon, on affiche les produits trouvés
            return new JsonResponse($this->renderView('/front/partials/_searchResult.html.twig', [
                    'products' => $products,
            ]));
        }  
    }
}
