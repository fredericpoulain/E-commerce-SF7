<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Yaml\Yaml;

readonly class NumberCategoriesFixtures
{

    private int $compteur;

    public function __construct(private ParameterBagInterface $parameterBag)
    {
        $this->compteur = 1;
    }
    public function getNumber()
    {
        $yamlFilePath = $this->parameterBag->get('categoriesFixtures');
        $allCategories = Yaml::parse(file_get_contents($yamlFilePath));

        return $this->countCategories($allCategories);

    }
    private function countCategories($array) {
        $count = 0;
        foreach ($array as $category) {
            $count++; // Compte la catégorie principale
            if (isset($category['childrenCategory']) && is_array($category['childrenCategory'])) {
                $count += $this->countCategories($category['childrenCategory']); // Compte les sous-catégories
            }
        }
        return $count;
    }
}