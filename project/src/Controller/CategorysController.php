<?php

namespace App\Controller;

use App\Entity\Categories;
use App\Repository\ProductsRepository;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CategorysController extends AbstractController
{
    #[Route('/categorie/{slug}', name: 'app_categorys')]
    public function index(Categories $categories): Response
    {
        $catParent = $categories->getCatParent();

//        //Si c'est une catégorie principale
//        if (is_null($catParent)){
//            return $this->render('categorys/categorys.html.twig', [
//                'category' => $categories,
//            ]);
//        }
//        return $this->redirectToRoute('app_home');
        $productsListContainsItems = !empty($categories->getProducts()->toArray());
        if ($productsListContainsItems){
            return $this->redirectToRoute('app_productsList', [
                'slug' => $categories->getSlug(),
            ]);
        }
        return $this->render('categorys/categorys.html.twig', [
            'category' => $categories,
            'productsListContainsItems' => $productsListContainsItems
        ]);

    }
    #[Route('/liste-produits/{slug}', name: 'app_productsList')]
    public function productsList(
        Categories              $categories,
        Request                $request,
        ProductsRepository      $productsRepository): Response
    {



//        $page = $request->query->getInt('page', 1);
//        $categoryID = $categories->getId();

        $products = $categories->getProducts()->toArray();


        return $this->render('categorys/productsList.html.twig', [
            'title' => $categories->getName(),
//            'slug' => $categories->getSlug(),
            'products' => $products
        ]);
    }
}
