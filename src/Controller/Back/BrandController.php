<?php

namespace App\Controller\Back;

use App\Entity\Brand;
use App\Form\BrandType;
use App\Form\Filter\BrandFilterType;
use App\Repository\BrandRepository;
use App\Repository\ProductRepository;
use App\Service\FileUploader;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/brand')]
#[IsGranted('ROLE_ADMIN')]
class BrandController extends AbstractController
{
    /**
     * Ce controller va servir à afficher la liste des marques 
     *
     * @param BrandRepository $brandRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param FilterBuilderUpdaterInterface $builderUpdater
     * @return Response
     */
    #[Route('/', name: 'app_brand_index', methods: ['GET'])]
    public function index(
        BrandRepository $brandRepository,
        PaginatorInterface $paginator, 
        Request $request,
        FilterBuilderUpdaterInterface $builderUpdater,
        ): Response
    {
        // On récupère toutes les marques
        $qb = $brandRepository->getQbAll();

        // on crée nos filtres de recherche de marque
        $filterForm = $this->createForm(BrandFilterType::class, null, [
            'method' => 'GET',
        ]);

        // On vérifie si la query a un paramètre du formFilter en cours, si oui, on l’ajoute dans le queryBuilder
        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->all($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

        // Pagination
        $brands = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('back/brand/index.html.twig', [
            'brands' => $brands,
            'filters' => $filterForm->createView(),
        ]);
    }

    #[Route('/new', name: 'app_brand_new', methods: ['GET', 'POST'])]
    /**
     * Ce controller va servir à l'ajout d'une marque
     *
     * @param Request $request
     * @param BrandRepository $brandRepository
     * @param FileUploader $fileUploader
     * @return Response
     */
    public function new(
        Request $request, 
        BrandRepository $brandRepository,
        FileUploader $fileUploader,
        ): Response
    {
        $brand = new Brand();

        // Creation du formulaire d'ajout d'une marque
        $form = $this->createForm(BrandType::class, $brand);

        // On inspecte les requêtes du formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // On ajoute le logo à la marque
            $data = $form->getData();
            if ($form->get('pathImage')->getData() !== null) {
                $file = $fileUploader->uploadFile(
                $form->get('pathImage')->getData()
                );
                $data->setPathImage($file);
            }
            // On met la marque à jour en bdd
            $brandRepository->add($brand, true);

            $this->addFlash(
                'success',
                'Votre marque a été ajoutée avec succès !'
            );
            return $this->redirectToRoute('app_brand_index', [], Response::HTTP_SEE_OTHER);
        }

            return $this->renderForm('back/brand/new.html.twig', [
                'brand' => $brand,
                'form' => $form,
            ]);
    }

    /**
     * Ce controller va servir à la modification d'une marque
     *
     * @param Request $request
     * @param Brand $brand
     * @param BrandRepository $brandRepository
     * @param FileUploader $fileUploader
     * @return Response
     */
    #[Route('/{slug}/edit', name: 'app_brand_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Brand $brand, BrandRepository $brandRepository, FileUploader $fileUploader): Response
    {
        // Creation du formulaire de marque
        $form = $this->createForm(BrandType::class, $brand);
        
        // On inspecte les requêtes du formulaire
        $form->handleRequest($request);

        // Si le form est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            if ($form->get('pathImage')->getData() !== null) {
                $file = $fileUploader->uploadFile(
                $form->get('pathImage')->getData()
                );
                $data->setPathImage($file);
            }    
             // On met la marque à jour en bdd
            $brandRepository->add($brand, true);

            $this->addFlash(
                'success',
                'Votre marque a été modifiée avec succès !'
            );

            return $this->redirectToRoute('app_brand_index', [], Response::HTTP_SEE_OTHER);
        } else {
            $this->addFlash(
                'error',
                $form->getErrors()
            );
        }

        return $this->renderForm('back/brand/edit.html.twig', [
            'brand' => $brand,
            'form' => $form,
        ]);
    }


    /**
     * Ce controller va servir à la suppression d'une marque
     *
     * @param Request $request
     * @param Brand $brand
     * @param BrandRepository $brandRepository
     * @return Response
     */
    #[Route('/{slug}/delete', name: 'app_brand_delete', methods: ['POST'])]
    public function delete(Request $request, Brand $brand, BrandRepository $brandRepository, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$brand->getId(), $request->request->get('_token'))) {
            foreach ($brand->getProducts() as $product) {
                $product->setActive(false);
                $productRepository->add($product, true);
            }
            $brandRepository->remove($brand, true);
        }

        return $this->redirectToRoute('app_brand_index', [], Response::HTTP_SEE_OTHER);
    }
}
