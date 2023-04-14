<?php

namespace App\Service\ShoppingCart;

use App\Entity\Basket;
use App\Entity\ContentShoppingCart;
use App\Entity\Customer;
use App\Entity\Product;
use App\Enum\StatusEnum;
use App\Repository\BasketRepository;
use App\Repository\ContentShoppingCartRepository;
use App\Repository\ProductRepository;
use App\Repository\StatusRepository;
use App\Service\PriceTaxInclService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Security;

class ShoppingCartService {

    protected $requestStack;
    protected $productRepository;
    protected $priceTaxInclService;
    protected $entityManager;
    protected $basketRepository;
    protected $contentShoppingCartRepository;
    protected $security;

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
        $session = $this->requestStack->getSession();
        // $this->resetSessionVariables($session);
        // dd('lol');
        $id = $product->getId();
        

        // Si panier pas encore crée, on en crée un et le stock en session,
        $customer = $this->security->getUser();
        if (!$session->has('basket')) {
            $shoppingCart = new Basket();
            $session->set('shoppingCart', $shoppingCart);
        } else {
            // Je récupère mon entité panier et mon entité contentshoppingCart
            $shoppingCart = $session->get('shoppingCart', []);
            $shoppingCart = $this->basketRepository->find($shoppingCart->getId());
        }
        
        // Si utilisateur connecter et panier crée on crée la panier en son nom
        if ($customer && !empty($shoppingCart) && !$shoppingCart->getCustomer()) {
            $shoppingCart->setCustomer($customer);
        };
        // On récupère notre panier
        $basket = $session->get('basket', []);

        
        // On crée un nouvelle ligne de panier
        $contentShoppingCart = new ContentShoppingCart();
        
        // On vérifie si le produit entré n'est pas déjà dans la panier
        if (!empty($basket[$id])) {
            $basket[$id]++;
            $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();
            
            foreach ($contentShoppingCarts as $oldContentShoppingCart) {                
                
                if ($oldContentShoppingCart->getProduct() === $product) {
                    $contentShoppingCart = $oldContentShoppingCart;
                }
            }          
        } else {
            // Sinon pas déjà dans la panier on l'ajoute ainsi que son prix et sa tva
            $basket[$id] = 1;
            $contentShoppingCart->setProduct($product);
            $contentShoppingCart->setPrice($product->getPriceExclVat());
            $contentShoppingCart->setTva($product->getTva());
        }

        // Peu importe si le produit est déjà dans la panier ou non on met à jour la quantité dans la ligne du panier
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
        // On récupère notre basket
        $basket = $session->get('basket', []);

        // Je récupère mon entité panier et mon entité contentshoppingCart
        $shoppingCart = $session->get('shoppingCart', []);
        $shoppingCart = $this->basketRepository->find($shoppingCart->getId());

        // Si j'ai le produit dans le panier et il n'est pas vide on supprime alors notre produit en session
        if (!empty($basket[$id])) {
            unset($basket[$id]);

            // puis en bdd
            $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();
            
            foreach ($contentShoppingCarts as $oldContentShoppingCart) {                
    
                if ($oldContentShoppingCart->getProduct() === $product) {
                    $this->contentShoppingCartRepository->remove($oldContentShoppingCart, true);
                }
            }    
        }

        // Si le panier est vide alors on supprime le panier
        if (empty($basket)) {
            $this->basketRepository->remove($shoppingCart, true);
            $session->remove('basket');
            $session->remove('shoppingCart');
        } else {
            // Sinon on met à jour notre basket
            $session->set('basket', $basket);
            $session->set('shoppingCart', $shoppingCart);
        }
        
    }
    public function getFullCart(): array {
        $session = $this->requestStack->getSession();

        $basket = $session->get('basket', []);

        $basketWithData = [];
        $quantityTotal = 0;
        foreach ($basket as $id => $quantity) {
        
            // On vérifie que le produit existe
            if ($this->productRepository->getProductShoppingCart($id) !== null) {
                $basketWithData[] = [
                    'product' => $this->productRepository->getProductShoppingCart($id),
                    'quantity' => $quantity,
                ];
                $quantityTotal+= $quantity;

            // Si admin supprime produit, on supprime le produits des paniers
            } else {
                if (!empty($basket)) {
                    unset($basket[$id]);
                }  
                if (empty($basket)) {
                    if (empty($basket)) {
                        $session->remove('basket');
                        $session->remove('shoppingCart');
                    } else {
                        // Sinon on met à jour notre basket
                        $session->set('basket', $basket);
                    }
                }
            }
        };
        // On enregistre la quantité totale dans la session
        $session->set('QTY', $quantityTotal);

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
            // On multiplie le prix du produit par sa quantité
            $totalItem = ($this->priceTaxInclService->calcPriceTaxIncl($product)) * $quantity;

            // On additionne le total de chaque ligne
            $total+= $totalItem;
        }
        return $total;
    }

    public function transformShoppingCartToBasketSession() {
        // On récupère la session
        $session = $this->requestStack->getSession();

        // On récupère notre panier de bdd et de session
        $basket = $session->get('basket', []);
        $shoppingCart = $session->get('shoppingCart', []);

        // S'il y a un panier d'achat en cours dans la session, je le récupère dans la base de données.
        if ($shoppingCart) {
            $shoppingCart = $this->basketRepository->find($shoppingCart->getId());
        }
        /** @var Customer $customer*/
        $customer = $this->security->getUser();

        // Si ancien panier existant on supprime le nouveau
        $oldShoppingCart = $this->basketRepository->findBasketWithCustomer($customer->getId());
        
        if (!empty($oldShoppingCart[0]) && ($oldShoppingCart[0] !== $shoppingCart)) {
            if ($shoppingCart) {
                $this->basketRepository->remove($shoppingCart, true);
            }
            $shoppingCart = $oldShoppingCart[0];

            // On recupere nos lignes de panier
            $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();

            // Et on set notre panier session avec les info du dernier panier que l'utilisateur n'a pas supprimé avant de se déconnecter
            foreach ($contentShoppingCarts as $contentShoppingCart) {  
                // Id du produit de la ligne
                $id = $contentShoppingCart->getProduct()->getId();              
                
                // Si le produit du panier n'est pas dans la session on l'ajoute
                if (empty($basket[$id])) {
                    $basket[$id] = $contentShoppingCart->getQuantity();
                } else {
                    // sinon on additionne la quantité de la session et celle de la ligne du panier
                    $basket[$id] += $contentShoppingCart->getQuantity();
                }
            }  
        }         
        // Si dernier panier existant
        if ($shoppingCart) {
            if (!$shoppingCart->getCustomer()) {
                $shoppingCart->setCustomer($customer);
                $this->basketRepository->add($shoppingCart, true);
            }
            // On recupere nos lignes de panier
            $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();
    
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
                    $contentShoppingCart->setProduct($product)
                        ->setPrice($product->getPriceExclVat())
                        ->setTva($product->getTva())
                        ->setQuantity($quantity)
                    ;
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
        }
    }
    
    public function subtractQuantity(Product $product) {
        $session = $this->requestStack->getSession();
        $id = $product->getId();
        // On récupere notre basket
        $basket = $session->get('basket', []);

        // Je recupere mon entité panier et mon entité contentshoppingCart
        $shoppingCart = $session->get('shoppingCart', []);
        $shoppingCart = $this->basketRepository->find($shoppingCart->getId());

        $contentShoppingCarts = $shoppingCart->getContentShoppingCarts();
        
        // Si le produit éxiste, on décrémente 
        if (!empty($basket[$id]) && $basket[$id] > 1) {
                $basket[$id]--;

                foreach ($contentShoppingCarts as $oldContentShoppingCart) {                
        
                    if ($oldContentShoppingCart->getProduct() === $product) {
                        $oldContentShoppingCart->setQuantity($basket[$id]);

                        $this->basketRepository->add($shoppingCart, true);
                    }
                }    
        } else {
            unset($basket[$id]);
            foreach ($contentShoppingCarts as $oldContentShoppingCart) {                
                
                if ($oldContentShoppingCart->getProduct() === $product) {
                    
                    $this->contentShoppingCartRepository->remove($oldContentShoppingCart, true);
                }
            }    
        }
          // Si le panier est vide alors on supprime le panier
          if (empty($basket)) {
            $this->basketRepository->remove($shoppingCart, true);
            $session->remove('basket');
            $session->remove('shoppingCart');
        } else {
            // Sinon on met à jour notre basket
            $session->set('basket', $basket);
            $session->set('shoppingCart', $shoppingCart);
        }
    }

    public function addUserToBasket() 
    {
        $session = $this->requestStack->getSession();

        /** @var Customer $user */
        $user = $this->security->getUser();
        if ($user) {         
            // On vérifie que l'utilisateur possède bien un panier
            if ($session->has('basket') && $session->has('shoppingCart')) {
                $shoppingCart = $session->get('shoppingCart', []);
                $shoppingCart = $this->basketRepository->find($shoppingCart->getId());
                $shoppingCart->setCustomer($user);
                $this->basketRepository->add($shoppingCart, true);
            }
        }
    }

    /**
     * Cette function va permettre de finaliser les commandes.
     *
     * @param Basket $order
     * @param StatusRepository $statusRepository
     * @return Basket
     */
    public function finalizeOrderInBdd(Basket $order, StatusRepository $statusRepository): Basket
{
    $session = $this->requestStack->getSession();

    /** @var Customer $customer*/
   $customer = $this->security->getUser();

    // Mise à jour du status de la commande et réinitialisation des variables de session du panier  
    if (is_null($order->getStatus())) {
        $status = $statusRepository->findOneBy(['name' => StatusEnum::ACCEPTER]);
        $order->setStatus($status);
        $this->resetSessionVariables($session);
        $this->basketRepository->add($order, true);
    }
    // On récupère la dernière commande de l'utilisateur
    $order = $this->basketRepository->findLastBasketWithCustomer($customer->getId(), 1);

    return $order[0];
}
    /**
     * Cette function va permettre de reset les variables de sessions
     *
     * @param Session $session
     * @return void
     */
    public function resetSessionVariables(Session $session) 
    { 
        $session->remove('basket');
        $session->remove('shoppingCart');   
    }
}
?>