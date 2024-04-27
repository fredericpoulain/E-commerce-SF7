<?php

namespace App\DataFixtures;


use App\Entity\ImageCategory;
use App\Entity\ImagesCategories;
use App\Service\NumberCategoriesFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImagesCategoriesFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(private readonly NumberCategoriesFixtures $numberCategoriesFixtures)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $numberCategories = $this->numberCategoriesFixtures->getNumber();

        for ($i=1; $i<=$numberCategories; $i++) {
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
