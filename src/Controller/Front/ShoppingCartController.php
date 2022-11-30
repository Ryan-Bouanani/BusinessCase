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
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/checkout')]
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

    #[Route('/address', 'app_checkout_address')]
    public function address(
        Request $request, 
        BasketRepository $basketRepository,
        AddressRepository $addressRepository,
        CustomerRepository $customerRepository,
        ShoppingCartService $shoppingCartService,
    ) 
    {          
            /** @var Customer $user*/
            $user = $this->getUser();
            if ($user) {

                if ($user->getAddress()) {
                    $address = $user->getAddress();
                    $formAddress = $this->createForm(AddressType::class, $address);
                } else {
                    $address = new Address();
                    $formAddress = $this->createForm(AddressType::class, $address);
                }

                // On récupère le dernier panier
                $order = $basketRepository->findBasketWithCustomer($user->getId());

                if ($order) {
                                
                    $formAddress->handleRequest($request);
                    if ($formAddress->isSubmitted() && $formAddress->isValid()) {

                        if ($user->getAddress()) {
                            return $this->redirectToRoute('app_checkout_address', [], Response::HTTP_SEE_OTHER);
                        }
                        $addressRepository->add($address, true);

                        $user->setAddress($address);
                        $customerRepository->add($user, true);

                        return $this->redirectToRoute('app_checkout_payment', [], Response::HTTP_SEE_OTHER);
                    } else {
                        $this->addFlash(
                            'error',
                            'Une erreur est survenue au sein de votre formulaire'
                        );
                    }
                } else {

                }

            } else {
                return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
            }

            if ($user->getAddress()) {
                return $this->render('front/shoppingCart/address.html.twig', [
                    'formAddress' => $formAddress->createView(),
                    'address' => $address,
                    'order' => $order,
                    // On calcul le montant du panier
                    'total' => $shoppingCartService->getTotal(),
                ]);
            } else {
                return $this->render('front/shoppingCart/address.html.twig', [
                    'formAddress' => $formAddress->createView(),
                    'oder' => $order,
                    // On calcul le montant du panier
                    'total' => $shoppingCartService->getTotal(),
                ]);
            }
    }

    #[Route('/payment', 'app_checkout_payment')]
    public function payment(
        BasketRepository $basketRepository,
        Request $request,
        ShoppingCartService $shoppingCartService,
    ) 
    {
        /** @var Customer $user*/
        $user = $this->getUser();

        // On récupère le dernier panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());

        if (!$user || !$order) {
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }  
        
        $order->setAddress($user->getAddress());
        $basketRepository->add($order, true);

        if ($user->getAddress() === NULL) {
            return $this->redirectToRoute('app_address', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(MeanOfPaymentType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
                    
            $order->setMeanOfPayment($form->get('meanOfPayment')->getData());
            $order->setBillingDate(new \DateTime());
            $basketRepository->add($order, true);

            return $this->redirectToRoute('app_checkout_resume', []);
        } else {
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

    

    #[Route('/resume', 'app_checkout_resume')]
    public function resume(
        BasketRepository $basketRepository,
        ShoppingCartService $shoppingCartService,
    ) {
        /** @var Customer $user*/
        $user = $this->getUser();

        // On récupère le dernier panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());

        if (!$user || !$order) {
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

    #[Route('/validate-order', 'app_validate_order')]
    public function validateOrder(
        BasketRepository $basketRepository,
        ShoppingCartService $shoppingCartService,
        StatusRepository $statusRepository,
        SessionInterface $session
    ) {
        /** @var Customer $user*/
        $user = $this->getUser();

        // On récupère le derniere panier de l'utilisateur
        $order = $basketRepository->findBasketWithCustomer($user->getId());

        if (!$user) {
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }    
        if ($order && is_null($order->getStatus())) {
            $status = $statusRepository->findOneBy(['name' => StatusEnum::ACCEPTER]);
            $order->setStatus($status);
            $session->remove('basket');
            $session->remove('shoppingCart');
            $basketRepository->add($order, true);
            
        }
        // On récupère la derniere commande de l'utilisateur
        $order = $basketRepository->findLastBasketWithCustomer($user->getId(), 1);        

        return $this->renderForm('front/shoppingCart/validateOrder.html.twig', [
            'items' => $order[0]->getContentShoppingCarts(),
            // On calcul le montant du panier
            'total' => $shoppingCartService->getTotal($order),
        ]);
    }


}