<?php

namespace App\DataFixtures;


use App\Entity\Categories;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Yaml\Yaml;


class CategoriesFixtures extends Fixture
{
    private int $compteur = 1;
    private int $compteurPourImage = 1;
    public function __construct(
        private readonly SluggerInterface $slugger,
        private readonly ParameterBagInterface $parameterBag,
    )
    {
    }

    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {

        $yamlFilePath = $this->parameterBag->get('categoriesFixtures');
        $categories = Yaml::parse(file_get_contents($yamlFilePath));

        //CreateCategories prend en paramètre le tableau des catégories, la catégorie parente (ou null si
        //les catégories sont des catégories principales) et le gestionnaire d’entités.
        $this->createCategories($categories, null, $manager);
        $manager->flush();
    }

    /**
     * @param $categories
     * @param $parentCategory
     * @param $entityManager
     * @return void
     *
     * La fonction parcourt le tableau des catégories et crée une nouvelle entité de catégorie pour chaque élément du tableau.
     * Si une catégorie parente est fournie, elle est définie en utilisant le setter setParent.
     * Si la catégorie a des sous-catégories, (if ($data['childrenCategory'])), la fonction est
     * appelée récursivement pour traiter les sous-catégories.
     */
    private function createCategories($categories, $parentCategory, $entityManager): void
    {
//        $compteur = 1;
//        $compteurPourImage = 1;

        foreach ($categories as $name => $data) {
            $category = new Categories();
            $category->setName($name);
            $category->setSlug($this->slugger->slug($category->getName())->lower());
            $category->setDescription($data['description']);
            $category->setSort($data['sort']);

            //si la catégorie n'a pas de parent, c'est ici que l'on va ajouter une référence
            // pour l'association des produits. un produit sera associé à la "dernière catégorie" dans la hiérarchie.
            if (!$data['childrenCategory']) {
                $this->addReference('cat-' . $this->compteur, $category);
                $this->compteur++;
            }
            //par contre ici on va créer un autre référence pour chaque catégorie.
            // Elles nous serviront pour associer toutes les catégories à une image
            $this->addReference('catForImage-' . $this->compteurPourImage, $category);
            $this->compteurPourImage++;
            if ($parentCategory) {
                $category->setCatParent($parentCategory);
            }
            $entityManager->persist($category);

            if ($data['childrenCategory']) {
                $this->createCategories($data['childrenCategory'], $category, $entityManager);
            }

        }
    }
}

//Catégorie : PC tour
//Catégorie : PC portable gamer
//Catégorie : Portable Mac
//Catégorie : Sacoche portable

//Catégorie : Tablette
//Catégorie : Processeur AMD
//Catégorie : Processeur INTEL
//Catégorie : Ram DDR4

//Catégorie : Ram DDR5
//Catégorie : Carte graphique
//Catégorie : disque SSD
//Catégorie : disque M2

//Catégorie : Disque dur magnetite
//Catégorie : Clé USB
//Catégorie : Carte mère
//Catégorie : Écran PC

//Catégorie : Clavier
//Catégorie : Souris
//Catégorie : Imprimantes