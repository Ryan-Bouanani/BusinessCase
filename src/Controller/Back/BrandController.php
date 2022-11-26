<?php

namespace App\Controller\Back;

use App\Entity\Brand;
use App\Form\BrandType;
use App\Form\Filter\BrandFilterType;
use App\Repository\BrandRepository;
use App\Service\FileUploader;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/brand')]
#[IsGranted('ROLE_ADMIN')]
class BrandController extends AbstractController
{
    #[Route('/', name: 'app_brand_index', methods: ['GET'])]
    public function index(
        BrandRepository $brandRepository,
        PaginatorInterface $paginator, 
        Request $request,
        FilterBuilderUpdaterInterface $builderUpdater,
        ): Response
    {
        // on récupère tout les produits
        $qb = $brandRepository->getQbAll();

        // on crée nos filtres de recherche
        $filterForm = $this->createForm(BrandFilterType::class, null, [
            'method' => 'GET',
        ]);

        // on vérifie si la query a un paramètre du formFilter en cours.Si oui, on l’ajoute dans le queryBuilder
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
public function new(
    Request $request, 
    BrandRepository $brandRepository,
    FileUploader $fileUploader,
    ): Response
{
    $brand = new Brand();
    $form = $this->createForm(BrandType::class, $brand);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        $data = $form->getData();
        if ($form->get('pathImage')->getData() !== null) {
            $file = $fileUploader->uploadFile(
            $form->get('pathImage')->getData()
            );
            $data->setPathImage($file);
        }
        $brandRepository->add($brand, true);

        $this->addFlash(
            'success',
            'Votre marque a été ajoutée avec succès !'
        );

        return $this->redirectToRoute('app_brand_index', [], Response::HTTP_SEE_OTHER);
    } else {
        $this->addFlash(
            'error',
            $form->getErrors()
        );
    }

        return $this->renderForm('back/brand/new.html.twig', [
            'brand' => $brand,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_brand_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Brand $brand, BrandRepository $brandRepository, FileUploader $fileUploader): Response
    {
        // Creation du formulaire de marque
        $form = $this->createForm(BrandType::class, $brand);
        
        // On inspecte les requettes du formulaire
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

    #[Route('/{id}/delete', name: 'app_brand_delete', methods: ['POST'])]
    public function delete(Request $request, Brand $brand, BrandRepository $brandRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$brand->getId(), $request->request->get('_token'))) {
            $brandRepository->remove($brand, true);
        }

        return $this->redirectToRoute('app_brand_index', [], Response::HTTP_SEE_OTHER);
    }
}
