<?php

namespace App\DataFixtures;


use App\Entity\ImagesProducts;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class ImagesProductsFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {

        for ($i=1; $i<193; $i++) {
            $image = new ImagesProducts();
            $image->setName('product-thumb-'. $i . '.png');
            $image->setProduct($this->getReference('prod-' . $i));
            $manager->persist($image);
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            ProductsFixture::class
        ];
    }
}
