<?php

namespace App\Controller\Front;

use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SitemapController extends AbstractController
{
    /**
     * Ce controller va servir aux robots d'indexation des moteurs de recherche de pouvoir lire le site plus intelligemment 
     *
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param BrandRepository $brandRepository
     * @return Response
     */
    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ["_format" => "xml"])]
    public function index(
        Request $request,
        ProductRepository $productRepository, 
        CategoryRepository $categoryRepository, 
        BrandRepository $brandRepository
        ): Response
    {
        // On récupère le nom d'hôte deouis l'url (http://127.0.0.1:8000 en local) 
        $hostName = $request->getSchemeAndHttpHost();

        // On initialise un tableau pour lister les URLs
        $urls = [];

        // ON ajoute les URLs "statiques"
        $urls[] = ['loc' => $this->generateUrl('app_home')];
        $urls[] = ['loc' => $this->generateUrl('app_login')];
        $urls[] = ['loc' => $this->generateUrl('app_register')];
        $urls[] = ['loc' => $this->generateUrl('app_customer')];
        $urls[] = ['loc' => $this->generateUrl('app_customer_personalData')];
        $urls[] = ['loc' => $this->generateUrl('app_customer_order')];
        $urls[] = ['loc' => $this->generateUrl('app_shoppingCart')];
        $urls[] = ['loc' => $this->generateUrl('app_checkout_login')];
        $urls[] = ['loc' => $this->generateUrl('app_checkout_register')];
        $urls[] = ['loc' => $this->generateUrl('checkout_address')];
        $urls[] = ['loc' => $this->generateUrl('checkout_choice_payment')];
        $urls[] = ['loc' => $this->generateUrl('checkout_resume')];

        foreach ($productRepository->findAll() as $product) {
            foreach ($product->getImages() as $image) {
                if ($image->getIsMain() === true) {
                    $images = [
                        'loc' => '/build/images/' . $image->getPath(), // URL to image
                        'title' => $product->getName() // Optional, text describing the image
                    ];
                }
            }
            $urls[] = [
                'loc' => $this->generateUrl('app_detail_product', [
                    'slug' => $product->getSlug(),
                ]),
                'lastmod' => $product->getDateAdded()->format('Y-m-d'),
                'image' => $images,
            ];
        }
        foreach ($categoryRepository->findAll() as $category) {
            $urls[] = [
                'loc' => $this->generateUrl('app_category_detail', [
                    'slug' => $category->getSlug(),
                ]),
            ];
        }
        foreach ($brandRepository->findAll() as $brand) {
            $urls[] = [
                'loc' => $this->generateUrl('app_brand_detail', [
                    'slug' => $brand->getSlug(),
                ]),
            ];
        }

        $response = new Response(
            $this->renderView('front/sitemap/index.html.twig', [
                'urls' => $urls,
                'hostname' => $hostName
            ]),
            200
        );

        // Ajout des entêtes
        $response->headers->set("Content-Type", "text/xml");

        // On envoie la réponse 
        return $response;
    }
}
