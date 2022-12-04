<?php

namespace App\Controller\Front;

use App\Entity\Category;
use App\Form\Filter\FrontProductFilterType;
use App\Repository\ProductRepository;
use App\Service\PriceTaxInclService;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    /**
     * Ce controller va servir à afficher les produits d'une catégorie
     *
     * @param Category $category
     * @param ProductRepository $productRepository
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param FilterBuilderUpdaterInterface $builderUpdater
     * @param PriceTaxInclService $priceTaxInclService
     * @return Response
     */
    #[Route('/{id}', name: 'app_category_detail')]
    public function index(
        Category $category, 
        ProductRepository $productRepository,
        Request $request,
        PaginatorInterface $paginator, 
        FilterBuilderUpdaterInterface $builderUpdater,
        PriceTaxInclService $priceTaxInclService,
        ): Response
    {
        // Récupere les produits de la catégorie
        if (is_null($category->getCategoryParent())) {
            $qb = $productRepository->getProductSameCategory($category->getId() , null,  true);
        } else {
            $qb = $productRepository->getProductSameCategory($category->getId());
        }

        // Creation du formulaire de filtre de produit
        $filterForm = $this->createForm(FrontProductFilterType::class, null, [
            'method' => 'GET',
        ]);
            
        // On vérifier si la query a un paramètre du formFilter en cours, si c’est le cas, on ajoute alors notre form dans le queryBuilder
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
        
        return $this->render('front/category/index.html.twig', [
            'category' => $category,
            'products' =>  $products,
            'filters' => $filterForm->createView(),
        ]);
    }
}
