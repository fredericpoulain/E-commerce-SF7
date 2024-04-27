<?php

namespace App\DataFixtures;


use App\Entity\ImageCategory;
use App\Entity\ImagesCategories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImagesCategoriesFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i=1; $i<27; $i++) {
            $image = new ImagesCategories();
            $image->setName('category-thumb-'. $i . '.png');
            $image->setCategory($this->getReference('catForImage-' . $i));
            $manager->persist($image);
        }
        $manager->flush();
    }
    public function getDependencies(): array
    {
        return [
            CategoriesFixtures::class
        ];
    }
}
