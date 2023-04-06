<?php

namespace App\Controller\Back;

use App\Entity\Address;
use App\Entity\Basket;
use App\Entity\ContentShoppingCart;
use App\Form\AddressType;
use App\Form\BasketType;
use App\Form\Filter\OrderFilterType;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('admin/basket')]
#[IsGranted('ROLE_ADMIN')]
class BasketController extends AbstractController
{
    /**
     * Ce controller va servir à afficher la liste des commandes 
     *
     * @param BasketRepository $basketRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param FilterBuilderUpdaterInterface $builderUpdater
     * @return Response
     */
    #[Route('/', name: 'app_basket_index', methods: ['GET'])]
    public function index(
        BasketRepository $basketRepository,
        PaginatorInterface $paginator, 
        Request $request,
        FilterBuilderUpdaterInterface $builderUpdater,
        ): Response

    {
        $qb = $basketRepository->getQbAll();

        // Creation du formulaire de filtre de commande
        $filterForm = $this->createForm(OrderFilterType::class, null, [
            'method' => 'GET',
        ]);

        // On vérifier si la query a un paramètre du formFilter en cours, si c’est le cas, on ajoute alors notre form dans le queryBuilder
        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->all($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

        // Pagination
        $baskets = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('back/basket/index.html.twig', [
            'baskets' => $baskets,
            'filters' => $filterForm->createView(),
        ]);
    }

    // /**
        //  * Ce controller va servir à l'ajout d'une commande
        //  *
        //  * @param Request $request
        //  * @param BasketRepository $basketRepository
        //  * @param AddressRepository $addressRepository
        //  * @return Response
        //  */
        // #[Route('/new', name: 'app_basket_new', methods: ['GET', 'POST'])]
        // public function new(Request $request, BasketRepository $basketRepository, AddressRepository $addressRepository): Response
        // {
        //     $basket = new Basket();
        //     // $address = new Address();

        //     // Creation du formulaire de commande
        //     $formBasket = $this->createForm(BasketType::class, $basket);
        //     // $formAddress = $this->createForm(AddressType::class, $address);

        //     // On inspecte les requettes du formulaire
        //     $formBasket->handleRequest($request);

        //     // Si le form est envoyé et valide
        //     if ($formBasket->isSubmitted() && $formBasket->isValid()) {

        //         // $basket->setAddress($address);
        //         // $addressRepository->add($address, true);

        //          // On met la commande en bdd
        //         $basketRepository->add($basket, true);

        //         $this->addFlash(
        //             'success',
        //             'Votre commande a été ajoutée avec succès !'
        //         );

        //         return $this->redirectToRoute('app_basket_index', [], Response::HTTP_SEE_OTHER);
        //     } else {
        //         $this->addFlash(
        //             'error',
        //             $formBasket->getErrors()
        //         );
        //     }

        //     return $this->renderForm('back/basket/new.html.twig', [
        //         'basket' => $basket,
        //         'formBasket' => $formBasket,
        //         // 'formAddress' => $formAddress,
        //     ]);
        // }
    // 

    // #[Route('/{id}', name: 'app_basket_show', methods: ['GET'])]
    // public function show(Basket $basket): Response
    // {
    //     return $this->render('back/basket/show.html.twig', [
    //         'basket' => $basket,
    //     ]);
    // }

    /**
     * Ce controller va servir la modification d'une commande
     *
     * @param Request $request
     * @param Basket $basket
     * @param BasketRepository $basketRepository
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_basket_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Basket $basket, BasketRepository $basketRepository): Response
    {
        $address = $basket->getAddress();
        // On récupere le contenue de chaques paniers
        $contentShoppingCarts = $basket->getContentShoppingCarts();

        // Creation des formulaires de commandes et d'adresses
        $formBasket = $this->createForm(BasketType::class, $basket, ['contentShoppingCarts' => $contentShoppingCarts]);
        $formAddress = $this->createForm(AddressType::class, $address);
        
        // On inspecte les requettes du formulaire
        $formBasket->handleRequest($request);

        // Si le form est envoyé et valide
        if ($formBasket->isSubmitted() && $formBasket->isValid()) {

            $count = 1;
            foreach ($contentShoppingCarts as $line) {
                $quantity = $formBasket->get('quantity' . $count)->getData();
                $line->setQuantity($quantity);
                $count++;
            }
            // On met la commande à jour en bdd
            $basketRepository->add($basket, true);

            $this->addFlash(
                'success',
                'Votre commande a été modifié avec succès !'
            );
            return $this->redirectToRoute('app_basket_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash(
                'error',
                $formBasket->getErrors()
            );
        }

        return $this->renderForm('back/basket/edit.html.twig', [
            'basket' => $basket,
            'formBasket' => $formBasket,
            'formAddress' => $formAddress,
        ]);
    }

    /**
     * Ce controller va servir à la suppression d'une commande 
     *
     * @param Request $request
     * @param Basket $basket
     * @param BasketRepository $basketRepository
     * @return Response
     */
    #[Route('/{id}/delete', name: 'app_basket_delete', methods: ['POST'])]
    public function delete(Request $request, Basket $basket, BasketRepository $basketRepository): Response
    {
        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete'.$basket->getId(), $request->request->get('_token'))) {
            // On supprime la commande en bdd
            $basketRepository->remove($basket, true);
        }
        return $this->redirectToRoute('app_basket_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * Ce controller va servir à supprimer un produit de la commande 
     *
     * @param ContentShoppingCart $contentShoppingCart
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
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

    /**
     * Ce controller va servir à ajouter un produit au sein de la commande
     *
     * @param Basket $basket
     * @param string $product
     * @param string $quantity
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ProductRepository $productRepository
     * @return Response
     */
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

                // On récupere le produit qui va être ajouté à la commande
                $product = $productRepository->findOneBy(['name' => $product]);
                
                // On récupere les lignes de la commande
                $contentShoppingCarts = $basket->getContentShoppingCarts();
                
                // Pour chaque ligne de la commande
                foreach ($contentShoppingCarts as $contentShoppingCart) {
                    
                    // On vérifie si le produit entré n'est pas déja dans la panier
                    if ($contentShoppingCart->getProduct() === $product) {
                        return new JsonResponse(['error' => 'Ce produit est déjà inclus dans votre panier']);
                    }
                }
            
                // On initialise les données de notre contentShoppingCart (ligne de commande)
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
                
                // Creation du formulaire de commande
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