<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


class HomeController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/', name: 'app_home')]
    public function index(ProductsRepository $productsRepository): Response
    {

        $randProducts = $productsRepository->findRandomProduct();
//        $categories = $categorieRepository->findAll();
        return $this->render('home/home.html.twig', [
            'randProducts' => $randProducts
        ]);
    }
}
