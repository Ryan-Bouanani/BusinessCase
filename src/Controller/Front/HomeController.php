<?php

namespace App\Controller\Front;

use App\Form\Filter\ProductSearchFilterType;
use App\Repository\BasketRepository;
use App\Repository\ProductRepository;
use Lexik\Bundle\FormFilterBundle\Filter\FilterBuilderUpdaterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    public function __construct(
        private ProductRepository $productRepository,
        private BasketRepository $basketRepository
    ) { }

    #[Route('/', name: 'app_home')]
    public function index(
        FilterBuilderUpdaterInterface $builderUpdater,
        Request $request
    ): Response
    {
        $qb = $this->productRepository->getQbAll();

        $filterForm = $this->createForm(
            ProductSearchFilterType::class,
            null,
            ['method' => 'GET']
        );

        if ($request->query->has($filterForm->getName())) {
            $filterForm->submit($request->query->get($filterForm->getName()));
            $builderUpdater->addFilterConditions($filterForm, $qb);
        }

        $newProducts = $this->productRepository->getNewAndTopRatedProduct('product.dateAdded');
        // $topRatedProducts = $this->productRepository->getNewAndTopRatedProduct('note');

;

        return $this->render('front/home/index.html.twig', [
            'filterSearchForm' => $filterForm->createView(),
            'newProducts' => $newProducts,
            // 'topRatedProducts' => $topRatedProducts,
        ]);
    }
}
