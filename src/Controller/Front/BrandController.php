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
    /**
     * Ce controller va servir à afficher les produits d'une marque
     *
     * @param Brand $brand
     * @param ProductRepository $productRepository
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param FilterBuilderUpdaterInterface $builderUpdater
     * @return Response
     */
    #[Route('/{slug}', name: 'app_brand_detail')]
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
        
        // Creation du formulaire de filtre de produit
        $filterForm = $this->createForm(FrontBrandFilterType::class, null, [
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
            12
        );
        return $this->render('front/brand/index.html.twig', [
            'brand' => $brand,
            'products' => $products,
            'filters' => $filterForm->createView(),
        ]);
    }
}
