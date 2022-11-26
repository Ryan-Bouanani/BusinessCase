<?php

namespace App\Controller\Front;

use App\Entity\Brand;
use App\Form\Filter\FrontBrandFilterType;
use App\Repository\ProductRepository;
use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/brand')]
class BrandController extends AbstractController
{
    #[Route('/{id}', name: 'app_brand_detail')]
    public function index(
        Brand $brand, 
        ProductRepository $productRepository,
        Request $request,
        PaginatorInterface $paginator, 
        FilterBuilderUpdaterInterface $builderUpdater,
    ): Response
    {

        // On récupère les produits de la marque
        $qb = $productRepository->getProductByBrand($brand->getId());
        
        $filterForm = $this->createForm(FrontBrandFilterType::class, null, [
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
        return $this->render('front/brand/index.html.twig', [
            'brand' => $brand,
            'products' => $products,
            'filters' => $filterForm->createView(),
        ]);
    }
}
