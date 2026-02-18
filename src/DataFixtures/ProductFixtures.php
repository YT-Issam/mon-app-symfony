<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Smknstd\FakerPicsumImages\FakerPicsumImagesProvider;

class ProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');
        $faker->addProvider(new FakerPicsumImagesProvider($faker));

        $command = 'rm -rf ' . dirname(__DIR__) . '/../public/uploads/products';

        $destDir = dirname(__DIR__) . '/../public/uploads/products';
        if (!is_dir($destDir)) {

            mkdir($destDir, 0775, true);

        } else {

            exec($command);
            mkdir($destDir, 0775, true);

        }

        for ($i = 0; $i < 10; $i++) {
            $product = new Product();

            $filePath = $faker->image(dir: '/tmp', width: 320, height: 240);

            if ($filePath) {
                $ext = pathinfo($filePath, PATHINFO_EXTENSION);
                $filename = $faker->uuid() . '.' . $ext;

                copy($filePath, $destDir . '/' . $filename);

                $product->setImageFilename($filename);
            }

            $product->setTitle($faker->words(3, true))
                ->setPrice($faker->numberBetween($min = 50, $max = 300))
                ->setDescription($faker->realText($maxNbChars = 200, $indexSize = 2))
                ->setCategory($this->getReference('category-' . rand(0, 5), Category::class))
            ;

            $manager->persist($product);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }
}

