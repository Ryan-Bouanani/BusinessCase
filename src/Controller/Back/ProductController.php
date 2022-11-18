<?php

namespace App\Controller\Back;

use App\Entity\Image;
use App\Entity\Product;
use App\Form\Filter\ProductFilterType;
use App\Form\ProductType;
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

#[Route('/admin/product')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(
        ProductRepository $productRepository, 
        PaginatorInterface $paginator, 
        Request $request,
        FilterBuilderUpdaterInterface $builderUpdater,
        ): Response
    {

        $qb = $productRepository->getQbAll();

        $filterForm = $this->createForm(ProductFilterType::class, null, [
            'method' => 'GET',
        ]);

        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->all($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

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

    #[Route('/new', name: 'app_product_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        ProductRepository $productRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {

        // Création d'un nouveau produit
        $product = new Product();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        // Si le form est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // On récupère les images transmises
            $images = $form->get('images')->getData();

            // On boucle sur les images
            foreach ($images as $key => $image) {
                 // On génère un nouveau nom de fichier
                 $fichier = md5(uniqid()) . '.' . $image->guessExtension();

                // On copie le fichier dans le dossier "build/images"
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                
                // On crée et stocke l'image dans la base de données
                $img = new Image();
                $img->setPath($fichier);

                // On rend la première image principale
                if ($key == 0) {
                    $img->setIsMain(true);
                }
                // On ajoute nos images au produit
                $product->addImage($img);
            }
            // On ajoute notre produit en bdd 
            $entityManager->persist($product);
            $productRepository->add($product, true);

            // On redirige l'administrateur vers la liste des produits
            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
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

    #[Route('/{id}/edit', name: 'app_product_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, ProductRepository $productRepository): Response
    {

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

             // On récupère les images transmises
             $images = $form->get('images')->getData();
            //  $isMain = $form->get('isMain')->getData();

             // On boucle sur les images
             foreach ($images as $key => $image) {
                // On génère un nouveau nom de fichier
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
 
                 // On copie le fichier dans le dossier "build/images"
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                 
                // On crée et stocke l'image dans la base de données
                $img = new Image();
                // $img->setName($fichier);
                $img->setPath($fichier);
                if ($key == 1) {
                    $img->setIsMain(true);
                }
                $product->addImage($img);

             }

            $productRepository->add($product, true);

            return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('back/product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_product_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {

        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            
            // $productImages = $product->getImages();
            // foreach ($productImages as $key => $image) {
            // // On récupere le nom de l'image
            // $path = $image->getPath();
            // // Puis on supprime l'image en local
            // unlink($this->getParameter('images_directory').'/'.$path);
            // }
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('app_product_index', [], Response::HTTP_SEE_OTHER);
    }

// 
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
            $path = $image->getPath();
            // Puis on supprime l'image en local
            unlink($this->getParameter('images_directory').'/'.$path);

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
