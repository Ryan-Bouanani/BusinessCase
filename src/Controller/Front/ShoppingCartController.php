<?php

namespace App\Controller\Front;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\ShoppingCart\ShoppingCartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/shoppingCart')]
class ShoppingCartController extends AbstractController
{
    #[Route('', name: 'app_shoppingCart')]
    public function index(ShoppingCartService $shoppingCartService): Response
    {

        return $this->render('front/shoppingCart/index.html.twig', [
            // On récupere et envoie notre panier 
            'items' => $shoppingCartService->getFullCart(),
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal(),
        ]);
    }

    // Ajouter un produit au panier
    #[Route('/add/{id}', name: 'app_shoppingCart_add', requirements: ['id' => '\d+'])]
    public function add(
       int $id, 
        ShoppingCartService $shoppingCartService,
        ProductRepository $productRepository
        )
    {
        $product = $productRepository->find($id);
        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'éxiste pas");
        }
        // On ajoute l'id du produit à notre panier
        $shoppingCartService->add($product);

        $shoppingCartService->getFullCart();

        return $this->redirectToRoute("app_detail_product", [
            'id' => $product->getId(),
        ]);
    }

    #[Route('/remove/{id}', 'app_shoppingCart_remove')]
    public function remove(Product $product, ShoppingCartService $shoppingCartService) {

        // On supprime le produit de notre panier
        $shoppingCartService->remove($product);

        return $this->redirectToRoute("app_shoppingCart");
    }

    #[Route('/addQuantity/{id}', 'app_shoppingCart_addQuantity')]
    public function addQuantity(Product $product, ShoppingCartService $shoppingCartService) {

        // On ajoute le produit de notre panier
        $shoppingCartService->add($product);

        return $this->redirectToRoute("app_shoppingCart");
    }
    #[Route('/substractQuantity/{id}', 'app_shoppingCart_substractQuantity')]
    public function substractQuantity(Product $product, ShoppingCartService $shoppingCartService) {

        // On supprime le produit de notre panier
        $shoppingCartService->substractQuantity($product);

        return $this->redirectToRoute("app_shoppingCart");
    }
}
