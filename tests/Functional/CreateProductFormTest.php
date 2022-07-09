<?php

namespace App\Tests\Functional;

use App\Entity\Category;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateProductFormTest extends WebTestCase
{
    public function testProductFormValidation(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        // retrieve the test user
        $testUser = $userRepository->findOneByUsername('admin');

        // simulate $testUser being logged in
        $client->loginUser($testUser);

        $crawler = $client->request('GET', '/store/product');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.card-header', 'Create new product');

        // Récuperer le formulaire

        $submitButton = $crawler->selectButton('Submit');
        $form = $submitButton->form();


        $form["product[name]"] = "Laptop Numero 1";
        $form["product[description]"] = "Description Numero 1";
        $form["product[price]"] = 2443;
        $form["product[quantity]"] = 100;
        $form["product[image]"]->upload("C:\Users\L390\Downloads\logo6.png");
        $form["product[category]"] = 1;
        
        // Soumettre le formulaire

        $client->submit($form);

        // Vérifier le status HTTP

        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);

        // Vérifier la présence du message de succés

       /* $this->assertSelectorTextContains(
            'div.alert.alert-success',
            'Your product was saved'
        );*/
    }
}
