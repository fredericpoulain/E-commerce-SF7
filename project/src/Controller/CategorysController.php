<?php

namespace App\Controller;

use App\Entity\Categories;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategorysController extends AbstractController
{
    #[Route('/categorie/{slug}', name: 'app_categorys')]
    public function index(Categories $category): Response
    {
        return $this->render('categorys/categorys.html.twig', [
            'category' => $category,
        ]);
    }
}
