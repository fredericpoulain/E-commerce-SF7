<?php

namespace App\DataFixtures;

use App\Entity\ImageProduct;
use App\Entity\Images;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImageProductFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i<193; $i++) {
            $image = new Images();
            $image->setName('product-thumb-'. $i . '.png');
            $image->setProduct($this->getReference('prod-' . $i));
            $manager->persist($image);
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            ProductFixture::class
        ];
    }
}
