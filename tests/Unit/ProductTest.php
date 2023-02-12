<?php

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Repository\BrandRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductTest extends KernelTestCase
{
    public function testEntityIsValid(): void
    {
        $container = static::getContainer();
        $categoryRepository = $container->get(CategoryRepository::class);
        $brandRepository = $container->get(BrandRepository::class);
        

        $brand = $brandRepository->findOneBy(['name' => 'Royal Canin']);
        $category = $categoryRepository->findOneBy(['name' => 'Alimentation pour chien']);

        $product = new Product();
        $product->setName('Product #1')
                ->setDescription('Croquette pour chien')
                ->setPriceExclVat('20')    
                ->setActive(false)
                ->setTva('20')      
                ->setBrand($brand)
                ->setCategory($category)
        ;

        $errors = $container->get('validator')->validate($product);
        $this->assertCount(0, $errors);
    }
}
