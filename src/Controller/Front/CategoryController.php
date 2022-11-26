<?php

namespace App\Controller\Front;

use App\Entity\Category;
use App\Form\Filter\FrontProductFilterType;
use App\Form\Filter\ProductFilterType;
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

        
        // dd($products->getItems());
        // $productsFilter = $products->getItems();
        $filterForm = $this->createForm(FrontProductFilterType::class, null, [
            'method' => 'GET',
            // 'priceExclVatService' => $this->get()
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
        
        return $this->render('front/category/index.html.twig', [
            'category' => $category,
            'products' =>  $products,
            'filters' => $filterForm->createView(),
        ]);
    }
}
