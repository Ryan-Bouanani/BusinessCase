<?php

namespace App\Controller\Front;

use App\Service\ShoppingCart\PaypalOperationService;
use App\Entity\Address;
use App\Entity\Product;
use App\Entity\Customer;
use App\Enum\StatusEnum;
use App\Form\AddressType;
use App\Form\MeanOfPaymentType;
use App\Repository\AddressRepository;
use App\Repository\BasketRepository;
use App\Repository\CustomerRepository;
use App\Repository\ImageRepository;
use App\Repository\PaypalPaymentRepository;
use App\Repository\ProductRepository;
use App\Repository\StatusRepository;
use App\Service\PriceTaxInclService;
use App\Service\ShoppingCart\ShoppingCartService;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\Common\Item;
use Omnipay\PayPal\PayPalItem;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            // On récupère et envoie notre panier 
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
        // On récupère le produit
        $product = $productRepository->find($id);
        // Si produit inexistant on renvoie une erreur
        if (!$product || !$product->isActive()) {
            throw $this->createNotFoundException("Le produit $id n’existe pas");
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
     * Ce controller va permettre d'ajouter une adresse pour l'utilisateur et sa commande
     *
     * @param Request $request
     * @param BasketRepository $basketRepository
     * @param AddressRepository $addressRepository
     * @param CustomerRepository $customerRepository
     * @param ShoppingCartService $shoppingCartService
     * @return void
     */
    #[Route('/address', 'checkout_address')]
    public function address(
        Request $request, 
        BasketRepository $basketRepository,
        AddressRepository $addressRepository,
        CustomerRepository $customerRepository,
        ShoppingCartService $shoppingCartService,
    ): Response
    {          
        // Si pas d'utilisateur, on redirige vers l'accueil
        /** @var Customer $user*/
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }
        
        // Si l'utilisateur à deja une adresse, on crée un form de modification
        // Sinon on Créer du formulaire d'ajout d'adresse
        $address = $user->getAddress() ?? New Address;
        $formAddress = $this->createForm(AddressType::class, $address);

        // On récupère le dernier panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());

        // Si dernier panier existant
        if (empty($order)) {
            // Si dernier panier non existant, redirection vers la page d'accueil
            return $this->redirectToRoute('app_home');
        }

        // On inspecte les requêtes du formulaire
        $formAddress->handleRequest($request);      
        // Si le formulaire est envoyé et valide
        if ($formAddress->isSubmitted() && $formAddress->isValid()) {                      
            // On met l'adresse de l'utilisateur en bdd
            $addressRepository->add($address, true);
            $user->setAddress($address);
            $customerRepository->add($user, true);

            if ($user->getAddress()) {
                return $this->redirectToRoute('checkout_address', [], Response::HTTP_SEE_OTHER);
            }

            // Puis on redirige vers la page suivante (paiement)
            return $this->redirectToRoute('checkout_choice_payment', [], Response::HTTP_SEE_OTHER);

        } elseif($formAddress->isSubmitted() && !$formAddress->isValid()) {
            // Si form non valide on renvoie une erreur
            $this->addFlash(
                'error',
                'Une erreur est survenue au sein de votre formulaire'
            );
        }
        
        // Rendu : Si l'utilisateur possède une adresse
        if ($user->getAddress()) {
            return $this->render('front/shoppingCart/address.html.twig', [
                'formAddress' => $formAddress->createView(),
                'order' => $order[0],
                // On calcul le montant du panier
                'total' => $shoppingCartService->getTotal(),
                'address' => $address,
            ]);
        } else {
                // Rendu : Si utilisateur possède pas d'adresse 
            return $this->render('front/shoppingCart/address.html.twig', [
                'formAddress' => $formAddress->createView(),
                'order' => $order[0],
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
    #[Route('/choice_payment', 'checkout_choice_payment')]
    public function choice_payment(
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
        if (!empty($order)) {
            // On ajoute l'adresse de l'utilisateur au panier
            $order[0]->setAddress($user->getAddress());
            $basketRepository->add($order[0], true);
        } else {
            // Si pas de dernier panier on redirige vers l'accueil
            return $this->redirectToRoute('app_home');
        }

        // Si l'utilisateur n'a pas d'adresse on le redirige vers la page d’ajout( d'adresse)
        if ($user->getAddress() === NULL) {
            return $this->redirectToRoute('app_address', [], Response::HTTP_SEE_OTHER);
        }

        // Creation du formulaire de moyen de paiement
        $form = $this->createForm(MeanOfPaymentType::class, $order[0]);
        // On inspecte les requêtes du formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
                    
            // On récupère et ajoute le moyen de paiement choisie par l'utilisateur et la date de facturation à la commande 
            $order[0]->setMeanOfPayment($form->get('meanOfPayment')->getData());
            $order[0]->setBillingDate(new \DateTime());

            // On met la commande à jour en bdd
            $basketRepository->add($order[0], true);

            // Puis on redirige à l'étape suivante
            return $this->redirectToRoute('checkout_resume', []);
        } 

        return $this->renderForm('front/shoppingCart/payment.html.twig', [
            'form' => $form,
            'order' => $order[0],
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
    #[Route('/resume', 'checkout_resume')]
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
        if (empty($order)) {
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }       

        return $this->renderForm('front/shoppingCart/resume.html.twig', [
            'order' => $order[0],
            // On récupere et envoie notre panier 
            'items' => $shoppingCartService->getFullCart(),
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal(),
        ]);
    }

    /**
     * Ce controller va servir à gérer le paiement de la commande via PayPal ou Stripe
     *
     * @param BasketRepository $basketRepository
     * @param ShoppingCartService $shoppingCartService
     * @param PaypalOperationService $paypalOperationService
    //  * @return void
     */
    #[Route('/payment', name: 'checkout_payment')]
    public function payment(
        BasketRepository $basketRepository,
        ShoppingCartService $shoppingCartService,
        PaypalOperationService $paypalOperationService,
        PriceTaxInclService $priceTaxInclService,
        Request $request,
    ) : Response
    {            
        // Si pas d'utilisateur, on redirige vers l'accueil
        /** @var Customer $user*/
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }
        // On récupère le dernier panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());
        if ($order[0]->getMeanOfPayment()->getDesignation() === 'Paypal') {

            $tokenValue = $request->query->get('_csrf_token');

            if (!$this->isCsrfTokenValid('payment' . $order[0]->getId(), $tokenValue)) {
                return new Response('Opération non autorisée', Response::HTTP_BAD_REQUEST, [
                    // 'content-type' => 'text/plain'
                ]);
            }

            // Create a PayPal payment

            // Add détails cart (product, quantity, price) for user
            $items = [];
            foreach ($shoppingCartService->getFullCart() as $item) {
                $itemPrice = $priceTaxInclService->calcPriceTaxIncl($item['product']->getPriceExclVat(), $item['product']->getTva(), $item['product']->getPromotion()->getPercentage());

                $items[] = new PayPalItem([
                    'name' => $item['product']->getName(), 
                    'price' => $itemPrice, 
                    'quantity' => $item['quantity'],
                ]);
            }

            // Generate the full URL for returnUrl and cancelUrl
            $returnUrl = $this->generateUrl('checkout_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
            $cancelUrl = $this->generateUrl('checkout_error', [], UrlGeneratorInterface::ABSOLUTE_URL);
            
            $response = $paypalOperationService->purchase(
                $shoppingCartService->getTotal(), 
                $_ENV['PAYPAL_CURRENCY'], 
                $returnUrl, 
                $cancelUrl,
                $items
            )->send();
    
            // Redirect the user to PayPal to complete the payment
            try {
                // dd($response, $items);
                if ($response->isRedirect()) {
                    $response->redirect();
                } else {
                    return $response->getMessage();
                }
            } catch (\Throwable $th) {
                return new Response($th->getMessage(), Response::HTTP_BAD_REQUEST, [
                    'content-type' => 'text/plain'
                ]);
            } 
        }
        return $this->redirectToRoute('checkout_success');
    }
    
    
    // Page d'erreur de la transaction
    #[Route('/error', name: 'checkout_error')]
    public function error(): Response
    {
        // Gestion de l'exception : on affiche l'erreur et on redirige l'utilisateur vers l'accueil
        $this->addFlash('error', 'Une erreur s\'est produite lors de la validation de votre commande. Veuillez réessayer plus tard.');
        return $this->redirectToRoute('checkout_resume');
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
    #[Route('/success', 'checkout_success', methods: ['GET'])]
    public function success(
        BasketRepository $basketRepository,
        ShoppingCartService $shoppingCartService,
        StatusRepository $statusRepository,
        PaypalPaymentRepository $paypalPaymentRepository,
        SessionInterface $session,
        Request $request,  
        PaypalOperationService $paypalOperationService  
        ) : Response
    {
        // Si pas d'utilisateur, on redirige vers l'accueil
        /** @var Customer $user*/
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_home');
        }

        // On récupère le dernier panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());
        if (empty($order)) {
            return $this->redirectToRoute('app_home');
        }
        
        // On vérifie que la commande à bien été payé et que la commande n'est pas déjà en bdd (rechargement de page)
        if ($paypalOperationService->isPaypalOrderUnpaid($order[0], $request)
        || $paypalOperationService->isExistingPaypalPayment($paypalPaymentRepository, $request)
        ) {
            return $this->redirectToRoute('app_home');
        }

        if ($order[0]->getMeanOfPayment()->getDesignation() === 'Paypal') {
            try {
                // On vérifie si la transaction s'est bien passer et on met les infos de la commande en bdd
                $paypalOperationService->completePurchaseAndSavePayment($request, $paypalPaymentRepository);
                // throw new InvalidResponseException('Testing the catch block');
            } catch (InvalidResponseException $e) {
                // Gestion de l'exception : on affiche l'erreur et on redirige l'utilisateur vers l'accueil
                $this->addFlash('error', 'Une erreur s\'est produite lors de la validation de votre commande. Veuillez réessayer plus tard.');
                return $this->redirectToRoute('checkout_resume');
            }
        }
        
        // Si dernier panier existant et status non null
        if (is_null($order[0]->getStatus())) {
            // On ajoute un status
            $status = $statusRepository->findOneBy(['name' => StatusEnum::ACCEPTER]);
            $order[0]->setStatus($status);
            // On reset nos variables de session 
            $shoppingCartService->resetSessionVariables($session);
            // On met la commande à jour en bdd
            $basketRepository->add($order[0], true);
        }
        // On récupère la dernière commande de l'utilisateur
        $order = $basketRepository->findLastBasketWithCustomer($user->getId(), 1);        

        return $this->renderForm('front/shoppingCart/success.html.twig', [
            'items' => $order[0]->getContentShoppingCarts(),
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal($order[0]),
            'message' => 'Votre commande à bien été enregistrée'
        ]);
    }
}
