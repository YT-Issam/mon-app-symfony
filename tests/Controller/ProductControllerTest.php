<?php

namespace App\Tests\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\InMemoryUser;

class ProductControllerTest extends WebTestCase
{
    private static ?int $id = null;

    public function testIndex(): void
    {
        $client = self::createClient();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/product');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testNewProduct(): void
    {
        $client = self::createClient();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/product/new');
        $buttonCrawlerNode = $crawler->selectButton('Save');

        $form = $buttonCrawlerNode->form();

        $form['product[title]'] = 'Jeans levis';
        $form['product[description]'] = 'Voici la description de mon produit.';
        $form['product[price]'] = 125.26;
        $form['product[category]']->select('Pantalon');

        $client->submit($form);

        $container = self::getContainer();
        $product = $container->get(ProductRepository::class)->findOneBy(['title' => 'Jeans levis']);
        self::$id = $product->getId();

        $this->assertResponseRedirects('/admin/product');
    }

    public function testEditProduct(): void
    {
        $client = self::createClient();
        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/product/' . self::$id . '/edit');
        $buttonCrawlerNode = $crawler->selectButton('Update');

        $form = $buttonCrawlerNode->form();

        $form['product[title]'] = 'Jeans levis demo';
        $form['product[description]'] = 'Voici la description de mon produit.';
        $form['product[price]'] = 125.26;
        $form['product[category]']->select('T-shirt');

        $client->submit($form);

        $this->assertResponseRedirects('/admin/product');
    }

    public function testShowProduct(): void
    {
        $client = self::createClient();

        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/product/' . self::$id);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
