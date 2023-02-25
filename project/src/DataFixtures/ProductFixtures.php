<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $product = new Product();
            $product
                ->setName('Product ' . $i)
                ->setPrice(mt_rand(10, 600));

            $manager->persist($product);
        }

        $manager->flush();
    }
}
