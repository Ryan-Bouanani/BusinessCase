<?php

namespace App\Controller\Back;

use App\Entity\Image;
use App\Entity\Product;
use App\Form\Filter\ProductFilterType;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/product')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    /**
     * Ce controller va servir à afficher la liste des produits 
     *
     * @param ProductRepository $productRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param FilterBuilderUpdaterInterface $builderUpdater
     * @return Response
     */
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(
        ProductRepository $productRepository, 
        PaginatorInterface $paginator, 
        Request $request,
        FilterBuilderUpdaterInterface $builderUpdater,
        ): Response
    {
        // on récupère tout les produits
        $qb = $productRepository->getQbAll();

        // on crée nos filtres de recherche de produit
        $filterForm = $this->createForm(ProductFilterType::class, null, [
            'method' => 'GET',
        ]);

        // on vérifie si la query a un paramètre du formFilter en cours, si oui, on l’ajoute dans le queryBuilder
        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->all($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }
        // Pagination
        $products = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('back/product/index.html.twig', [
            'products' => $products,
            'filters' => $filterForm->createView(),
        ]);
    }

    /**
     * Ce controller va servir à l'ajout d'un nouveau produit
     *
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param FileUploader $fileUploader
     * @return Response
     */
    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        ProductRepository $productRepository,
        FileUploader $fileUploader,
    ): Response
    {
        // Création d'un nouveau produit
        $product = new Product();

        // Creation du formulaire de produit
        $form = $this->createForm(ProductType::class, $product);
        
        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le form est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les images transmises par l'utilisateur
            $images = $form->get('images')->getData();

             // On boucle sur les images
             foreach ($images as $key => $image) {
                if ($images !== null) {
                    $file = $fileUploader->uploadFile(
                    $image
                    );
                    // On crée et stocke l'image dans la base de données
                    $img = new Image();
                    $img->setPath($file);
                    if ($key == 1) {
                        $img->setIsMain(true);
                    }
                    $product->addImage($img);
                }                          
            }
            // On met le produit en bdd
            $productRepository->add($product, true);

            $this->addFlash(
                'success',
                'Votre produit a été ajouté avec succès !'
            );

            // // On boucle sur les images
            // foreach ($images as $key => $image) {
            //      // On génère un nouveau nom de fichier
            //      $fichier = md5(uniqid()) . '.' . $image->guessExtension();

            //     // On copie le fichier dans le dossier "build/images"
            //     $image->move(
            //         $this->getParameter('images_directory'),
            //         $fichier
            //     );
                
            //     // On crée et stocke l'image dans la base de données
            //     $img = new Image();
            //     $img->setPath($fichier);

            //     // On rend la première image principale
            //     if ($key == 0) {
            //         $img->setIsMain(true);
            //     }
            //     // On ajoute nos images au produit
            //     $product->addImage($img);
            // }
            // // On ajoute notre produit en bdd 
            // $entityManager->persist($product);
            // $productRepository->add($product, true);

            // On redirige l'administrateur vers la liste des produits
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash(
                'error',
                $form->getErrors()
            );
        }

        return $this->renderForm('back/product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {

        return $this->render('back/product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * Ce controller va servir à la modification d'un produit
     *
     * @param Request $request
     * @param Product $product
     * @param ProductRepository $productRepository
     * @param FileUploader $fileUploader
     * @return Response
     */
    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request, 
        Product $product, 
        ProductRepository $productRepository,
        FileUploader $fileUploader,
        ): Response
    {
        // Creation du formulaire de produit
        $form = $this->createForm(ProductType::class, $product);
        
        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le form est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {

             // On récupère les images transmises
             $images = $form->get('images')->getData();

            // On boucle sur les images
            foreach ($images as $key => $image) {
                if ($images !== null) {
                    $file = $fileUploader->uploadFile(
                    $image
                    );
                    // On crée et stocke l'image dans la base de données
                    $img = new Image();
                    $img->setPath($file);
                    if ($key == 1) {
                        $img->setIsMain(true);
                    }
                    $product->addImage($img);
                }                          
            }
            // On met le produit à jour en bdd
            $productRepository->add($product, true);

            $this->addFlash(
                'success',
                'Votre produit a été modifié avec succès !'
            );

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash(
                'error',
                $form->getErrors()
            );
        }

        return $this->renderForm('back/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    /**
     * Ce controller va servir à la suppression d'un produit
     *
     * @param Request $request
     * @param Product $product
     * @param ProductRepository $productRepository
     * @return Response
     */
    #[Route('/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            
            // $productImages = $product->getImages();
            // foreach ($productImages as $key => $image) {
            // On récupere le nom de l'image
            // $path = $image->getPath();
            // Puis on supprime l'image en local
            // unlink($this->getParameter('images_directory').'/'.$path);
            // }
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }


    /**
     * Ce controller va servir à la suppresson d'une image d'un produit
     *
     * @param Image $image
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    #[Route('/delete/image/{id}', name: 'app_product_deleteImage', methods: ['DELETE'])]
    public function deleteImage(
        Image $image, 
        Request $request,
        EntityManagerInterface $entityManager,
        ): Response
    {
        
        $data = json_decode($request->getContent(), true);
        
        // On vérifie si le token est valide
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $data['_token'])) {

            // On récupere le nom de l'image
            // $path = $image->getPath();
            // Puis on supprime l'image en local
            // unlink($this->getParameter('images_directory').'/'.$path);

            // Si on supprime l'image principal alors on la remplace
            if ($image->getIsMain() == true) {
                $product = $image->getProduct();
                foreach ($product->getImages() as $newImageIsMain) {
                    if ($newImageIsMain->getIsMain() !== true) { 
                        $newImageIsMain->setIsMain(true);
                        break;
                    }
                }
            }
            // On supprime l'image en BDD
            $entityManager->remove($image);
            $entityManager->flush();

            // On répond en JSON
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token Invalide'], 400);
        }
    }
}
