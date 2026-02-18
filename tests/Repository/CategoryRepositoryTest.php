<?php

namespace App\Tests\Repository;

use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryRepositoryTest extends KernelTestCase
{
    public function testFindAllCategory(): void
    {
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        $categories = count($container->get(CategoryRepository::class)->findAll());
        $this->assertEquals(6, $categories);
    }

    public function testFindOneByTitleCategory(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $category[] = $container->get(CategoryRepository::class)->findOneBy(['title' => 'shoes']);
        $this->assertEquals(1,count($category));
    }
}

