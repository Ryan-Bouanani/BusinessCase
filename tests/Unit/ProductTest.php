<?php

namespace App\Tests\Unit;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use App\Tests\BaseTestCase;

class ProductTest extends BaseTestCase
{

    public function testEntityIsValid(): void
    {
        $container = static::getContainer();
        $categoryRepository = $container->get(CategoryRepository::class);
        $brandRepository = $container->get(BrandRepository::class);
        

        $brand = $brandRepository->findOneBy(['name' => 'Royal Canin']);
        // $category = $categoryRepository->findOneBy(['name' => 'Alimentation pour chien']);
        $category = new Category();
        $category->setName('CateTestProduct');

        $categoryRepository->add($category, true);

        $product = new Product();
        $product->setName('Product #1')
                ->setDescription('Croquette pour chien')
                ->setPriceExclVat('20')    
                ->setActive(false)
                ->setTva('20')      
                ->setBrand($brand)
                ->setCategory($category)
        ;
        $this->assertValidationErrorsCount($product, 0);
    }
}
