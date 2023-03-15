<?php

namespace App\Test\Back\Controller;

use App\Entity\Category;
use App\Entity\Customer;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CategoryControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private CategoryRepository $repository;
    private string $path = '/category/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = (static::getContainer()->get('doctrine'))->getRepository(Category::class);

        foreach ($this->repository->findAll() as $object) {
            $this->repository->remove($object, true);
        }
    }

    public function testIfListCategoryIsSuccessful(): void
    {
        $urlGenerator = $this->client->getContainer()->get('router');
        $entitymanager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entitymanager->find(Customer::class, 2);

        $this->client->loginUser($user);

        $this->client->request('GET', $urlGenerator->generate('app_category_index'));

        $this->assertResponseIsSuccessful();
        self::assertRouteSame('app_category_index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testIfCreateAnCategoryIsSuccessful(): void
    {
        $urlGenerator = $this->client->getContainer()->get('router');
        $entitymanager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entitymanager->find(Customer::class, 2);

        $this->client->loginUser($user);

        $originalNumObjectsInRepository = count($this->repository->findAll());

        // $this->markTestIncomplete();
        $crawler = $this->client->request(Request::METHOD_GET, $urlGenerator->generate('app_category_new'));

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name=category]')->form([
            'category[name]' => 'Testing',
            // 'category[categoryParent]' => floatval(2),
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.success', 'Votre catégorie a été ajoutée avec succès !');

        $this->assertRouteSame('app_category_index');

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));
    }

    // public function testShow(): void
    // {
    //     $this->markTestIncomplete();
    //     $fixture = new Category();
    //     $fixture->setName('My Title');
    //     $fixture->setCategoryParent('My Title');

    //     $this->repository->add($fixture, true);

    //     $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

    //     self::assertResponseStatusCodeSame(200);
    //     self::assertPageTitleContains('Category');

    //     // Use assertions to check that the properties are properly displayed.
    // }

    public function testIfUpdateAnCategoryIsSuccessful(): void
    {
        $urlGenerator = $this->client->getContainer()->get('router');
        $entitymanager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entitymanager->find(Customer::class, 2);

        $this->client->loginUser($user);

        $fixture = new Category();
        $fixture->setName('Edit Test');
        $this->repository->add($fixture, true);
        
        $crawler = $this->client->request(
            Request::METHOD_GET,
             $urlGenerator->generate('app_category_edit', [
                'slug' => $fixture->getSlug(),
             ])
        );

        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[name=category]')->form( [
            'category[name]' => 'Edit Test Work',
        ]);

        $this->client->submit($form);

        self::assertResponseStatusCodeSame(Response::HTTP_FOUND);
       
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.success', 'Votre catégorie a été modifiée avec succès !');

        $this->assertRouteSame('app_category_index');

        $fixture = $this->repository->findAll();

        // self::assertSame('Edit Test Work', $fixture[0]->getName());
        self::assertSame('Edit Test Work', end($fixture)->getName());
    }

    public function testIfRemoveAnCategoryIsSuccessful(): void
    {
        $urlGenerator = $this->client->getContainer()->get('router');
        $entitymanager = $this->client->getContainer()->get('doctrine.orm.entity_manager');

        $user = $entitymanager->find(Customer::class, 2);

        $this->client->loginUser($user);

        $originalNumObjectsInRepository = count($this->repository->findAll());

        $fixture = new Category();
        $fixture->setName('Delete Test');

        $this->repository->add($fixture, true);

        self::assertSame($originalNumObjectsInRepository + 1, count($this->repository->findAll()));

        $this->client->request(
            Request::METHOD_POST, 
            $urlGenerator->generate('app_category_edit', [
            'slug' => $fixture->getSlug()
            ])
        );
        $this->assertResponseIsSuccessful();

        $this->client->submitForm('Supprimer');

        
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.success', 'Votre catégorie a été supprimée avec succès !');
        
        $this->assertRouteSame('app_category_index');

        self::assertSame($originalNumObjectsInRepository, count($this->repository->findAll()));
    }
}
