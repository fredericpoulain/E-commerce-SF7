<?php

namespace App\Controller;

use App\Entity\Products;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends AbstractController
{
    #[Route('/produits/{slug}', name: 'app_products')]
    public function index(Products $product): Response
    {
        $imagesArray = $product->getImages()->toArray();
        return $this->render('products/products.html.twig', [
            'product' => $product,
            'imagesArray'=>$imagesArray
        ]);
    }
}
