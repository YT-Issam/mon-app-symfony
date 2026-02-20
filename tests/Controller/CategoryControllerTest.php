<?php

namespace Controller;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\InMemoryUser;

class CategoryControllerTest extends WebTestCase
{
    private static ?int $id = null;

    public function testIndex(): void
    {
        $client = self::createClient();

        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/category');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testNewCategory(): void
    {
        $client = self::createClient();

        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/category/new');

        $buttonCrawlerNode = $crawler->selectButton('Save');
        $form = $buttonCrawlerNode->form();

        $form['category[title]'] = 'New Category';

        $client->submit($form);

        $container = self::getContainer();
        $category = $container->get(CategoryRepository::class)->findOneBy(['title' => 'New Category']);
        self::$id = $category->getId();

        $this->assertResponseRedirects('/admin/category');
    }

    public function testEditCategory(): void
    {
        $client = self::createClient();

        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $crawler = $client->request('GET', '/admin/category/' . self::$id . '/edit');

        $buttonCrawlerNode = $crawler->selectButton('Update');
        $form = $buttonCrawlerNode->form();

        $form['category[title]'] = 'New Category test';

        $client->submit($form);

        $this->assertResponseRedirects('/admin/category');
    }

    public function testShowCategory(): void
    {
        $client = self::createClient();

        $user = new InMemoryUser('admin', 'password', ['ROLE_ADMIN']);
        $client->loginUser($user);

        $client->request('GET', '/admin/category/' . self::$id);

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
