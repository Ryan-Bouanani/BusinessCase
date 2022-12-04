<?php

namespace App\Controller\Front;

use App\Entity\Address;
use App\Entity\Product;
use App\Entity\Customer;
use App\Enum\StatusEnum;
use App\Form\AddressType;
use App\Form\MeanOfPaymentType;
use App\Repository\AddressRepository;
use App\Repository\BasketRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
use App\Repository\StatusRepository;
use App\Service\ShoppingCart\ShoppingCartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkout')]
class ShoppingCartController extends AbstractController
{
    /**
     * Ce controller va servir à afficher la panier de l'utilisateur
     *
     * @param ShoppingCartService $shoppingCartService
     * @return Response
     */
    #[Route('', name: 'app_shoppingCart', methods: ['GET'])]
    public function index(ShoppingCartService $shoppingCartService): Response
    {

        return $this->render('front/shoppingCart/index.html.twig', [
            // On récupere et envoie notre panier 
            'items' => $shoppingCartService->getFullCart(),
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal(),
        ]);
    }
    
    /**
     * Ce controller va servir à ajouter un produit au panier
     *
     * @param integer $id
     * @param ShoppingCartService $shoppingCartService
     * @param ProductRepository $productRepository
     * @param Request $request
     * @return void
     */
    #[Route('/add/{id}', name: 'app_shoppingCart_add', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function add(
        int $id, 
        ShoppingCartService $shoppingCartService,
        ProductRepository $productRepository,
        Request $request,
        )
    {
        // On récupere le produit
        $product = $productRepository->find($id);
        // Si produit inexistant on renvoie une erreur
        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'éxiste pas");
        }
        // On ajoute l'id du produit à notre panier
        $shoppingCartService->add($product);

        // On met à jour la quantité du panier
        $shoppingCartService->getFullCart();

        // On redirige vers la page ou est l'utilisateur
        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }


    /**
     * Ce controller va servir à supprimer un produit du panier
     *
     * @param Product $product
     * @param ShoppingCartService $shoppingCartService
     * @return void
     */
    #[Route('/remove/{id}', 'app_shoppingCart_remove')]
    public function remove(
        Product $product, 
        ShoppingCartService $shoppingCartService
        ) {

        // On supprime le produit de notre panier
        $shoppingCartService->remove($product);

        return $this->redirectToRoute("app_shoppingCart");
    }



    // /**
    //  * Ce controller va servir à ajouter un produit déja existant au panier
    //  *
    //  * @param Product $product
    //  * @param ShoppingCartService $shoppingCartService
    //  * @return void
    //  */
    // #[Route('/addQuantity/{id}', 'app_shoppingCart_addQuantity')]
    // public function addQuantity(Product $product, ShoppingCartService $shoppingCartService) {

    //     // On ajoute le produit de notre panier
    //     $shoppingCartService->add($product);

    //      // On met à jour la quantité du panier
    //      $shoppingCartService->getFullCart();

    //     return $this->redirectToRoute("app_shoppingCart");
    // }

    
    /**
     * Ce controller va servir à retirer un produit du panier
     *
     * @param Product $product
     * @param ShoppingCartService $shoppingCartService
     * @param Request $request
     * @return void
     */
    #[Route('/substractQuantity/{id}', 'app_shoppingCart_substractQuantity', methods: ['GET'])]
    public function substractQuantity(
        Product $product, 
        ShoppingCartService $shoppingCartService,
        Request $request,
        ) 
    {

        // On enleve un fois le produit de notre panier
        $shoppingCartService->substractQuantity($product);

        // On redirige vers la page ou est l'utilisateur
        $route = $request->headers->get('referer');
        return $this->redirect($route);
    }

    /**
     * Ce controller va permettre d'ajouter une adressse pour l'utilisateur et sa commande
     *
     * @param Request $request
     * @param BasketRepository $basketRepository
     * @param AddressRepository $addressRepository
     * @param CustomerRepository $customerRepository
     * @param ShoppingCartService $shoppingCartService
     * @return void
     */
    #[Route('/address', 'app_checkout_address')]
    public function address(
        Request $request, 
        BasketRepository $basketRepository,
        AddressRepository $addressRepository,
        CustomerRepository $customerRepository,
        ShoppingCartService $shoppingCartService,
    ) 
    {          
            // Si pas d'utilisateur, on redirige vers l'accueil
            /** @var Customer $user*/
            $user = $this->getUser();
            if (!$user) {
                return $this->redirectToRoute('app_home');
            }
            
            // Si l'utilisateur à déja une adresse, on crée un form de modification
            if ($user->getAddress()) {
                $address = $user->getAddress();
                $formAddress = $this->createForm(AddressType::class, $address);
            } else {
                // Sinon on Créer du formulaire d'ajout d'adresse
                $address = new Address();
                $formAddress = $this->createForm(AddressType::class, $address);
            }

            // On récupère le dernier panier de l'utilisateur
            $order = $basketRepository->findBasketWithCustomer($user->getId());

            // Si dernier panier existant
            if ($order) {
                // On inspecte les requettes du formulaire
                $formAddress->handleRequest($request);

                // Si le formulaire est envoyé et valide
                if ($formAddress->isSubmitted() && $formAddress->isValid()) {                      
                    if ($user->getAddress()) {
                        return $this->redirectToRoute('app_checkout_address', [], Response::HTTP_SEE_OTHER);
                    }

                    // On met l'adresse de l'utilisateur en bdd
                    $addressRepository->add($address, true);
                    $user->setAddress($address);
                    $customerRepository->add($user, true);

                    // Puis on redirige vers la page suivante (paiement)
                    return $this->redirectToRoute('app_checkout_payment', [], Response::HTTP_SEE_OTHER);
                } elseif($formAddress->isSubmitted() && !$formAddress->isValid()) {
                    // Si form non valide on renvoie une erreur
                    $this->addFlash(
                        'error',
                        'Une erreur est survenue au sein de votre formulaire'
                    );
                }
            } else {
                // Si pas de dernier panier on redirige vers l'accueil
                return $this->redirectToRoute('app_home');
            }
            
            // Rendu : Si l'utilisateur possède une adresse
            if ($user->getAddress()) {
                return $this->render('front/shoppingCart/address.html.twig', [
                    'formAddress' => $formAddress->createView(),
                    'order' => $order,
                    // On calcul le montant du panier
                    'total' => $shoppingCartService->getTotal(),
                    'address' => $address,
                ]);
            } else {
                 // Rendu : Si utilisateur possède pas d'adresse 
                return $this->render('front/shoppingCart/address.html.twig', [
                    'formAddress' => $formAddress->createView(),
                    'oder' => $order,
                    // On calcul le montant du panier
                    'total' => $shoppingCartService->getTotal(),
                ]);
            }
    }

    /**
     * Ce controller va permettre d'ajouter un moyen de paiement pour la commande de l'utilisateur
     *
     * @param BasketRepository $basketRepository
     * @param Request $request
     * @param ShoppingCartService $shoppingCartService
     * @return void
     */
    #[Route('/payment', 'app_checkout_payment')]
    public function payment(
        BasketRepository $basketRepository,
        Request $request,
        ShoppingCartService $shoppingCartService,
    ) 
    {   
        // Si pas d'utilisateur, on redirige vers l'accueil
        /** @var Customer $user*/
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        // On récupère le dernier panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());
     
        // Si dernier panier existant
        if ($order) {
            // On ajoute l'adresse de l'utilisateur au panier
            $order->setAddress($user->getAddress());
            $basketRepository->add($order, true);
        } else {
            // Si pas de dernier panier on redirige vers l'accueil
            return $this->redirectToRoute('app_home');
        }

        // Si l'utilisateur n'a pas d'adresse on le redirige vers la page d'ajou( d'adresse)
        if ($user->getAddress() === NULL) {
            return $this->redirectToRoute('app_address', [], Response::HTTP_SEE_OTHER);
        }

        // Creation du formulaire de moyen de paiement
        $form = $this->createForm(MeanOfPaymentType::class, $order);
        
        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
                    
            // On récupere et ajoute le moyen de paiement choisie par l'utilisateur et la date de facturation à la commande 
            $order->setMeanOfPayment($form->get('meanOfPayment')->getData());
            $order->setBillingDate(new \DateTime());

            // On met la commande à jour en bdd
            $basketRepository->add($order, true);

            // Puis on redirige à l'étape suivante
            return $this->redirectToRoute('app_checkout_resume', []);
        } elseif($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash(
                'error',
                'Veuillez sélectionner un moyen de paiement'
            );
        }

        return $this->renderForm('front/shoppingCart/payment.html.twig', [
            'form' => $form,
            'order' => $order,
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal(),
        ]);
    }

    

    /**
     * Ce controller va servir à afficher le resumé de la commande
     *
     * @param BasketRepository $basketRepository
     * @param ShoppingCartService $shoppingCartService
     * @return void
     */
    #[Route('/resume', 'app_checkout_resume')]
    public function resume(
        BasketRepository $basketRepository,
        ShoppingCartService $shoppingCartService,
    ) 
    {
        // Si pas d'utilisateur, on redirige vers l'accueil
        /** @var Customer $user*/
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        // On récupère le dernier panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());

        // Si pas de dernier panier on redirige vers l'accueil
        if (!$order) {
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }        

        return $this->renderForm('front/shoppingCart/resume.html.twig', [
            'order' => $order,
            // On récupere et envoie notre panier 
            'items' => $shoppingCartService->getFullCart(),
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal(),
        ]);
    }

    /**
     * Ce controller va servir à confirmer que la commande de l'utilisateur à bien été prise en compte
     *
     * @param BasketRepository $basketRepository
     * @param ShoppingCartService $shoppingCartService
     * @param StatusRepository $statusRepository
     * @param SessionInterface $session
     * @return void
     */
    #[Route('/validate-order', 'app_validate_order', methods: ['GET'])]
    public function validateOrder(
        BasketRepository $basketRepository,
        ShoppingCartService $shoppingCartService,
        StatusRepository $statusRepository,
        SessionInterface $session
    ) 
    {
        // Si pas d'utilisateur, on redirige vers l'accueil
        /** @var Customer $user*/
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        // On récupère le derniere panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());

        // Si dernier panier existant et status non null
        if ($order && is_null($order->getStatus())) {
            // On qjoute un status
            $status = $statusRepository->findOneBy(['name' => StatusEnum::ACCEPTER]);
            $order->setStatus($status);
            // On reset nos variables de session
            $session->remove('basket');
            $session->remove('shoppingCart');
            // On met la commande à jour en bdd
            $basketRepository->add($order, true);
            
        }
        // On récupère la derniere commande de l'utilisateur
        $order = $basketRepository->findLastBasketWithCustomer($user->getId(), 1);        

        return $this->renderForm('front/shoppingCart/validateOrder.html.twig', [
            'items' => $order[0]->getContentShoppingCarts(),
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal($order[0]),
        ]);
    }
}