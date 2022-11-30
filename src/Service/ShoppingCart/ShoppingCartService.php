<?php

namespace App\Service\ShoppingCart;

use App\Entity\Basket;
use App\Entity\ContentShoppingCart;
use App\Entity\Product;
use App\Repository\BasketRepository;
use App\Repository\ContentShoppingCartRepository;
use App\Repository\ProductRepository;
use App\Service\PriceTaxInclService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class ShoppingCartService {

    protected $requestStack;
    protected $productRepository;
    protected $priceTaxInclService;
    protected $entityManager;
    protected $basketRepository;
    protected $contentShoppingCart;
    protected $request;

    public function __construct(
        RequestStack $requestStack,
        ProductRepository $productRepository,
        PriceTaxInclService $priceTaxInclService,
        EntityManagerInterface $entityManager,
        BasketRepository $basketRepository,
        ContentShoppingCartRepository $contentShoppingCartRepository,
        Security $security,
        )
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
        $this->priceTaxInclService = $priceTaxInclService;
        $this->entityManager = $entityManager;
        $this->basketRepository = $basketRepository;
        $this->contentShoppingCartRepository = $contentShoppingCartRepository;
        $this->security = $security;
    }

    public function add(Product $product) {
        $id = $product->getId();
        
        $session = $this->requestStack->getSession();
        // dd($session->get('shoppingCart', []));

        // $session->remove('basket');
        // $session->remove('shoppingCart');
        // $session->remove('QTY');
        // dd($session);

        // Si panier pas encore crée, on en crée un et le stock en session,
        $customer = $this->security->getUser();
        if (!$session->has('basket')) {
            $shoppingCart = new Basket();
            $session->set('shoppingCart', $shoppingCart);
            // dd($shoppingCart);
        } else {
            
            // Je recupere mon entité panier et mon entité contentshoppingCart
            $shoppingCart = $session->get('shoppingCart', []);
            $shoppingCart = $this->basketRepository->find($shoppingCart->getId());
            // dd($shoppingCart);
        }
        
        // Si utilisateur connecter et panier crée on cree la panier en son nom
        if ($customer && !empty($shoppingCart) && !$shoppingCart->getCustomer()) {
            $shoppingCart->setCustomer($customer);
        };
        // On récupere notre panier
        $basket = $session->get('basket', []);

        
        // On crée un nouvelle ligne de panier
        $contentShoppingCart = new ContentShoppingCart();
        
        // On vérifie si le produit entré n'est pas déja dans la panier
        if (!empty($basket[$id])) {
            $basket[$id]++;
            $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();
            
            foreach ($contentShoppingCarts as $oldContentShoppingCart) {                
                
                if ($oldContentShoppingCart->getProduct() === $product) {
                    $contentShoppingCart = $oldContentShoppingCart;
                }
            }          
        } else {
            // Sinon pas déja dans la panier on l'ajoute ainsi que son prix et sa tva
            $basket[$id] = 1;
            $contentShoppingCart->setProduct($product);
            $contentShoppingCart->setPrice($product->getPriceExclVat());
            $contentShoppingCart->setTva($product->getTva());
        }

        // Peu importe si le produit est déja dans la panier ou non on met à jour la quantité dans la ligne du panier
        $contentShoppingCart->setQuantity( $basket[$id]);
        
        // On ajoute la ligne de panier au panier
        $shoppingCart->addContentShoppingCart($contentShoppingCart);

        
        // Puis on push la panier et sa nouvelle ligne en bdd
        $this->basketRepository->add($shoppingCart, true);
        $this->contentShoppingCartRepository->add($contentShoppingCart, true);
        
        
        // Et on met à jour panier et on ajoute la new ligne du panier en bdd
        $session->set('basket', $basket);
        $session->set('shoppingCart', $shoppingCart);
    }



    public function remove(Product $product) {
        $session = $this->requestStack->getSession();

        $id = $product->getId();
        // On récupere notre basket
        $basket = $session->get('basket', []);

        // Je recupere mon entité panier et mon entité contentshoppingCart
        $shoppingCart = $session->get('shoppingCart', []);
        $shoppingCart = $this->basketRepository->find($shoppingCart->getId());

        // Si pas vide on supprime notre produit en session
        if (!empty($basket[$id])) {
            unset($basket[$id]);

            // puis en bdd
            $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();
            
            foreach ($contentShoppingCarts as $oldContentShoppingCart) {                
    
                if ($oldContentShoppingCart->getProduct() === $product) {
                    $this->contentShoppingCartRepository->remove($oldContentShoppingCart);
                }
            }    
        }

        // Si le panier est vide alors on supprime le panier
        if (empty($basket)) {
            $this->basketRepository->remove($shoppingCart);
            $session->remove('basket');
            $session->remove('shoppingCart');
        } else {
            // Sinon on met à jour notre basket
            $session->set('basket', $basket);
            $session->set('shoppingCart', $shoppingCart);
        }
        // On met à jour en bdd
        $this->entityManager->flush();
        
    }
    public function getFullCart(): array {
        $session = $this->requestStack->getSession();

        $basket = $session->get('basket', []);

        $basketWithData = [];
        $quantityToal = 0;
        foreach ($basket as $id => $quantity) {
            $basketWithData[] = [
                'product' => $this->productRepository->getProductShoppingCart($id),
                'quantity' => $quantity,
            ];
            $quantityToal+= $quantity;
        };
        // On enregistre la quantité totale dans la session
        $session->set('QTY', $quantityToal);

        return $basketWithData;
    }
    public function getTotal(Basket $order = null): float {

        $total = 0;

        if ($order) {
            $items = $order->getContentShoppingCarts();
        } else {
            $items = $this->getFullCart();
        }
        foreach ($items as $item) {

            if (gettype($items) !== 'array') {
                $product = $item->getProduct();
                $quantity = $item->getQuantity();
            } else {
                $product = $item['product'];
                $quantity = $item['quantity'];
            }
            if ($product->getPromotion()) {

                // On multiplie le prix du produit par sa quantité
                $totalItem = ($this->priceTaxInclService->calcPriceTaxIncl($product->getPriceExclVat(), $product->getTva(), $product->getPromotion()->getPercentage())) * $quantity;

            } else {
                 // On multiplie le prix du produit par sa quantité
                 $totalItem = ($this->priceTaxInclService->calcPriceTaxIncl($product->getPriceExclVat(), $product->getTva())) * $quantity;
            }
            // On additione le total de chaque ligne
            $total+= $totalItem;

        }
        return $total;
    }
    public function transformShoppingCartToBasketSesion() {
        // On recupere la session
        $session = $this->requestStack->getSession();

        // On recup notre panier vide
        $basket = $session->get('basket', []);
        // dd($basket);
        $shoppingCart = $session->get('shoppingCart', []);

        
        // on recup nos ligne de panier
        $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();
        
        // et on set notre panier session avec les info du dernier panier que l'utilisateur n'a pas supprimé avant de se déconnecter
        foreach ($contentShoppingCarts as $contentShoppingCart) {  
            // Id du produit de la ligne
            $id = $contentShoppingCart->getProduct()->getId();              
            // dd($session->get('basket', []), $session->get('shoppingCart', []), $shoppingCart);
            
            // Si le produit du panier n'est pas dans la session on l'ajoute
            if (empty($basket[$id])) {
                $basket[$id] = $contentShoppingCart->getQuantity();
            } else {
                // sinon on additionne la quantité de la session et celle de la ligne du panier
                $basket[$id] += $contentShoppingCart->getQuantity();
            }
        }  

        foreach ($basket as $id => $quantity) {

            $notInShoppingCart = true;
            foreach ($contentShoppingCarts as $contentShoppingCart) {
                // Si le produit de la session est déja dans la panier
                if ($id === $contentShoppingCart->getProduct()->getId()) {
                    $notInShoppingCart = false;
                }
            }
            // Si produit session n'est pas dans le panier alors on l'ajoute
            if ($notInShoppingCart) {
                $product = $this->productRepository->getProductShoppingCart($id);
                $contentShoppingCart = new ContentShoppingCart();
                $contentShoppingCart->setProduct($product);
                $contentShoppingCart->setPrice($product->getPriceExclVat());
                $contentShoppingCart->setTva($product->getTva());
                $contentShoppingCart->setQuantity($quantity);
                $shoppingCart->addContentShoppingCart($contentShoppingCart);

                // Puis on met à jour en bdd le panier et sa/ses lignes si il y'a un changement
                $this->basketRepository->add($shoppingCart, true);
                $this->contentShoppingCartRepository->add($contentShoppingCart, true);
            }
        }

        

        // Et on met à jour le panier de session
        $session->set('basket', $basket);
        $session->set('shoppingCart', $shoppingCart);

        // Met à jour la quantité
        $this->getFullCart();

        // dd( $session->get('shoppingCart', []), $shoppingCart);
    }
    
    public function substractQuantity(Product $product) {
        $session = $this->requestStack->getSession();
        $id = $product->getId();
        // On récupere notre basket
        $basket = $session->get('basket', []);

        // Je recupere mon entité panier et mon entité contentshoppingCart
        $shoppingCart = $session->get('shoppingCart', []);
        $shoppingCart = $this->basketRepository->find($shoppingCart->getId());

        $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();
        
        // Si le produit éxiste, on décrémente 
        if (!empty($basket[$id])) {
            if ($basket[$id] > 1) {
                $basket[$id]--;

                foreach ($contentShoppingCarts as $oldContentShoppingCart) {                
        
                    if ($oldContentShoppingCart->getProduct() === $product) {
                        $oldContentShoppingCart->setQuantity($basket[$id]);

                        $this->basketRepository->add($shoppingCart, true);
                    }
                }    
            }
        }

        
        // Et on met à jour notre basket
        $session->set('basket', $basket);
        $session->set('shoppingCart', $shoppingCart);
    }
}
?>