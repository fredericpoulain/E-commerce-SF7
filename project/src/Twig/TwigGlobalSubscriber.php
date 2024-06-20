<?php

namespace App\Twig;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

readonly class TwigGlobalSubscriber implements EventSubscriberInterface
{
    public function __construct(
        Private Environment            $twig,
        Private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws Exception
     */
    public function injectGlobalDataCategory(ControllerEvent $event): void
    {
        //requête sql :
        $data = $this->entityManager->getConnection()
            ->executeQuery('SELECT categories.*, images_categories.name AS nameImage FROM categories 
        JOIN images_categories on categories.id = images_categories.category_id ')
            ->fetchAllAssociative();

        $dataCategory = $this->groupedCategory($data, null);

        // On injecte les données dans une variable globale de Twig
        $this->twig->addGlobal('globalDataCategory', $dataCategory);
    }

    private function groupedCategory($categories, $parentId)
    {
        $output = [];
        foreach ($categories as $category) {
            if ($category['cat_parent_id'] === $parentId) {
                $children = $this->groupedCategory($categories, $category['id']);
                $output[$category['name']] = [
                    'description' => $category['description'],
                    'slug' => $category['slug'],
                    'nameImage' => $category['nameImage'],
                    'childrenCategory' => $children
                ];
            }
        }
        return $output;
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => 'injectGlobalDataCategory'];
    }
}