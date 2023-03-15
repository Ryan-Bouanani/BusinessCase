<?php

namespace App\Controller\Back;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Form\Filter\CategoryFilterType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/category')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    /**
     * Ce controller va servir à afficher la liste des catégories
     *
     * @param CategoryRepository $categoryRepository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @param FilterBuilderUpdaterInterface $builderUpdater
     * @return Response
     */
    #[Route('/', name: 'app_category_index', methods: ['GET'])]
    public function index(
        CategoryRepository $categoryRepository,
        PaginatorInterface $paginator,
        Request $request,
        FilterBuilderUpdaterInterface $builderUpdater,
        ): Response
    {
        // on récupère toutes les catégories
        $qb = $categoryRepository->getQbAll();
        
        // Création du formulaire de filtres de recherche
        $filterForm = $this->createForm(CategoryFilterType::class, null, [
            'method' => 'GET',
        ]);
        // On vérifie si la query a un paramètre du formFilter en cours.Si oui, on l’ajoute dans le queryBuilder
        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->all($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

        // Pagination
        $categories = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );
        return $this->render('back/category/index.html.twig', [
            'categories' => $categories,
            'filters' => $filterForm->createView(),
        ]);
    }

    /**
     * Ce controller va servir à ajouter une nouvelle catégorie
     *
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    #[Route('/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $category = new Category();

        // Creation du formulaire de catéhorie
        $form = $this->createForm(CategoryType::class, $category);
       
        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le form est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {

             // On ajoute la catégorie en bdd
            $categoryRepository->add($category, true);

            $this->addFlash(
                'success',
                'Votre catégorie a été ajoutée avec succès !'
            );

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_FOUND);
        } else {
            $this->addFlash(
                'error',
                $form->getErrors()
            );
        }
        return $this->renderForm('back/category/new.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    /**
     * Ce controller va servir à la modification d'une catégorie
     *
     * @param Request $request
     * @param Category $category
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    #[Route('/{slug}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, CategoryRepository $categoryRepository): Response
    {
        // Creation du formulaire de catégorie
        $form = $this->createForm(CategoryType::class, $category);

        // On inspecte les requettes du formulaire
        $form->handleRequest($request);

        // Si le form est envoyé et valide
        if ($form->isSubmitted() && $form->isValid()) {

            // On met la catégorie à jour en bdd
            $categoryRepository->add($category, true);

            $this->addFlash(
                'success',
                'Votre catégorie a été modifiée avec succès !'
            );

            return $this->redirectToRoute('app_category_index', [], Response::HTTP_FOUND);
        }
        return $this->renderForm('back/category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    /**
     * Ce controller va servir à la suppression d'une catégorie
     *
     * @param Request $request
     * @param Category $category
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    #[Route('/{slug}/delete', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, CategoryRepository $categoryRepository, ProductRepository $productRepository): Response
    {
        if (!$this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_category_edit', [
                'slug' => $category->getSlug(),
            ]);   
        }

        // On set active à false pour tout les produits de la catégorie suprimée
        if ($category->getCategoryParent() === null) {
            $categories = $category->getCategoryChildren(); 
        } else {
            $categories = [$category];
        }

        foreach ($categories as $cat) {
            foreach ($cat->getProducts() as $product) {
                $product->setActive(false);
                $productRepository->add($product, true);
            }
        }

        // Remove category
        $categoryRepository->remove($category, true);

        $this->addFlash(
            'success',
            'Votre catégorie a été supprimée avec succès !'
        );

        return $this->redirectToRoute('app_category_index', [], Response::HTTP_FOUND);

    }
}
