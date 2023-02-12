<?php

namespace App\Tests\Front\Controller;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SecurityControllerTest extends WebTestCase 
{
    public function testDisplayLogin() 
    {
        $client = static::createClient();
        $client->request('GET', '/connexion');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSelectorTextContains('h1', 'Connexion');
        $this->assertSelectorNotExists('.error');
    }

    public function testIfLoginIsSuccessfull(): void 
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get('router');

        $crawler = $client->request('GET', $urlGenerator->generate('app_login'));

        // $form = $crawler->selectButton('Connexion')->form([
        $form = $crawler->filter("form[name=login]")->form([
            "username" => "LaTerreEstPlate",
            "password" => "Issou2021"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame('app_login');
    }

    public function testIfLoginFailWhenPasswordIsWrong(): void
    {
        $client = static::createClient();

        /** @var UrlGeneratorInterface $urlGenerator */
        $urlGenerator = $client->getContainer()->get('router');

        $crawler = $client->request('GET', $urlGenerator->generate('app_login'));

        // $form = $crawler->selectButton('Connexion')->form([
        $form = $crawler->filter("form[name=login]")->form([
            "username" => "LaTerreEstPlate",
            "password" => "Issou2021_"
        ]);

        $client->submit($form);

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        $client->followRedirect();

        $this->assertRouteSame('app_login');
        
        $this->assertSelectorExists('.error');
    }
}
