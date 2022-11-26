<?php

namespace App\Controller\Front;

use App\Entity\Address;
use App\Entity\Product;
use App\Entity\Customer;
use App\Form\AddressType;
use App\Repository\AddressRepository;
use App\Repository\BasketRepository;
use App\Repository\CustomerRepository;
use App\Repository\ProductRepository;
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
        ShoppingCartService $shoppingCartService, 
        SessionInterface $session, 
        Request $request, 
        AddressRepository $addressRepository,
        CustomerRepository $customerRepository,
        EntityManagerInterface $entityManager
        ) {

        if ($session->get('basket', [])) {

            /** @var Customer $user*/
            $user = $this->getUser();
            if ($user) {
                if ($user->getAddress()) {
                    $formAddress = $this->createForm(AddressType::class, $user->getAddress());
                    $userAddress = $user->getAddress();
                } else {
                    $address = new Address();
                    $formAddress = $this->createForm(AddressType::class, $address);
                }
                $formAddress->handleRequest($request);
                if ($formAddress->isSubmitted() && $formAddress->isValid()) {
                    $addressRepository->add($address, true);
                    $user->setAddress($address);
                    $customerRepository->add($user, true);
                    return $this->redirectToRoute('app_checkout_payment', [], Response::HTTP_SEE_OTHER);
                }

            }
        } else {
            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        if ($userAddress) {
            return $this->render('front/shoppingCart/address.html.twig', [
                'formAddress' => $formAddress->createView(),
                'address' => $userAddress,
            ]);
        } else {
            return $this->render('front/shoppingCart/address.html.twig', [
                'formAddress' => $formAddress->createView(),
            ]);
        }
    }
    #[Route('/payment', 'app_checkout_payment')]
    public function payment() {



        // return $this->redirectToRoute("app_shoppingCart");
        return $this->renderForm('front/shoppingCart/payment.html.twig', [

        ]);
    }
}
