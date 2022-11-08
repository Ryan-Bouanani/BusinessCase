<?php

namespace App\Controller\Back;

use App\Entity\Address;
use App\Entity\Basket;
use App\Entity\ContentShoppingCart;
use App\Form\AddressType;
use App\Form\BasketType;
use App\Form\Filter\OrderFilterType;
use App\Repository\AddressRepository;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/basket')]
class BasketController extends AbstractController
{
    #[Route('/', name: 'app_basket_index', methods: ['GET'])]
    public function index(
        BasketRepository $basketRepository,
        PaginatorInterface $paginator, 
        Request $request,
        FilterBuilderUpdaterInterface $builderUpdater,
        ): Response

    {
        $qb = $basketRepository->getQbAll();

        $filterForm = $this->createForm(OrderFilterType::class, null, [
            'method' => 'GET',
        ]);

        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->all($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

        $orders = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('back/basket/index.html.twig', [
            'orders' => $orders,
            'filters' => $filterForm->createView(),
        ]);
    }

    #[Route('/new', name: 'app_basket_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BasketRepository $basketRepository, AddressRepository $addressRepository): Response
    {
        $basket = new Basket();
        // $address = new Address();

        $formBasket = $this->createForm(BasketType::class, $basket);
        // $formAddress = $this->createForm(AddressType::class, $address);
        $formBasket->handleRequest($request);

        if ($formBasket->isSubmitted() && $formBasket->isValid()) {

            // $basket->setAddress($address);
            // $addressRepository->add($address, true);
            $basketRepository->add($basket, true);


            return $this->redirectToRoute('app_basket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/basket/new.html.twig', [
            'basket' => $basket,
            'formBasket' => $formBasket,
            // 'formAddress' => $formAddress,
        ]);
    }

    #[Route('/{id}', name: 'app_basket_show', methods: ['GET'])]
    public function show(Basket $basket): Response
    {
        return $this->render('back/basket/show.html.twig', [
            'basket' => $basket,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_basket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Basket $basket, BasketRepository $basketRepository): Response
    {

        $address = $basket->getAddress();
        // On récupere le contenue de chaques paniers
        $contentShoppingCarts = $basket->getContentShoppingCarts();

        // On crée le formulaire et on lui envoie les lignes de chaque paniers
        $formBasket = $this->createForm(BasketType::class, $basket, ['contentShoppingCarts' => $contentShoppingCarts]);

        $formAddress = $this->createForm(AddressType::class, $address);
        // Et on écoute le formulaire
        $formBasket->handleRequest($request);

        if ($formBasket->isSubmitted() && $formBasket->isValid()) {

            $count = 1;
            foreach ($contentShoppingCarts as $line) {
                $quantity = $formBasket->get('quantity' . $count)->getData();
                $line->setQuantity($quantity);
                $count++;
            }

            $basketRepository->add($basket, true);
            // if (empty($contentShoppingCarts)) {
            //     // Si commande vide on supprime la commande
            //     $basketRepository->remove($basket);
            // };

            return $this->redirectToRoute('app_basket_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/basket/edit.html.twig', [
            'basket' => $basket,
            'formBasket' => $formBasket,
            'formAddress' => $formAddress,
        ]);
    }

    #[Route('/{id}', name: 'app_basket_delete', methods: ['POST'])]
    public function delete(Request $request, Basket $basket, BasketRepository $basketRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$basket->getId(), $request->request->get('_token'))) {
            $basketRepository->remove($basket, true);
        }

        return $this->redirectToRoute('app_basket_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/delete/lineShoppingCart/{id}', name: 'app_basket_deleteLineContentShoppingCart', methods: ['DELETE'])]
    public function deleteLineShoppingCart(
        ContentShoppingCart $contentShoppingCart, 
        Request $request,
        EntityManagerInterface $entityManager,
        ): Response
    {
        $data = json_decode($request->getContent(), true);
      
        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $contentShoppingCart->getId(), $data['_token'])) {

            // On supprime la ligne en BDD
            $entityManager->remove($contentShoppingCart);
            $entityManager->flush();
            // On répond en JSON
            // dd($contentShoppingCart);
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }

    #[Route('/add/contentShoppingCart/{id}/{product?}/{quantity?}', name: 'app_basket_addContentShoppingCart', methods: ['POST'])]
    public function addContentShoppingCart(
        Basket $basket, 
        string $product,
        string $quantity,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository
        ): Response
        {
            $data = json_decode($request->getContent(), true);
            
            // On vérifie si le token est valide
            if ($this->isCsrfTokenValid('addContentShoppingCart' . $basket->getId(), $data['_token'])) {

                $product = $productRepository->findOneBy(['title' => $product]);
                
                $contentShoppingCarts = $basket->getContentShoppingCarts();
                // dd($basket->getContentShoppingCarts());
                
                foreach ($contentShoppingCarts as $contentShoppingCart) {
                    
                    // On vérifie si le produit entré n'est pas déja dans la panier
                    if ($contentShoppingCart->getProduct() === $product) {
                        return new JsonResponse(['error' => 'Ce produit est déjà inclus dans votre panier']);
                    }
                }
            
                // On initialise les données de notre contentShoppingCart
                $newContentshoppingCart = new ContentShoppingCart();
                $newContentshoppingCart->setProduct($product);
                $newContentshoppingCart->setQuantity($quantity);
                $newContentshoppingCart->setPrice($product->getPriceExclVat());
                $newContentshoppingCart->setTva($product->getTva());
                $basket->addContentShoppingCart($newContentshoppingCart);

                // Et on push notre nouveau contentShoppingCart et notre panier à jour
                $entityManager->persist($newContentshoppingCart);
                $entityManager->persist($basket);
                $entityManager->flush();
                
                $formBasket = $this->createForm(BasketType::class, $basket, ['contentShoppingCarts' => $contentShoppingCarts]);

            // On répond en JSON
            // Sinon, on affiche les produits trouvés
            return new JsonResponse($this->renderView('back/partials/_tableContentShoppingCart.html.twig', [
            'basket' => $basket,
            'formBasket' => $formBasket->createView(),
            ]));
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}