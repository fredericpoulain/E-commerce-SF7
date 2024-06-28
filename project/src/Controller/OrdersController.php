<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\OrdersDetails;
use App\Entity\ShippingAddresses;
use App\Repository\BillingAddressesRepository;
use App\Repository\ProductsRepository;
use App\Repository\ShippingAddressesRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/orders', name: 'app_orders_')]
class OrdersController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(
        SessionInterface $session,
        ProductsRepository $productsRepository,
        EntityManagerInterface $em,
        ShippingAddressesRepository $shippingAddressesRepository,
        BillingAddressesRepository $billingAddressesRepository): Response
    {
//        dd('ddddd');
        $this->denyAccessUnlessGranted('ROLE_USER');

        $cart = $session->get('cart', []);
//        dd($cart);
        if ($cart === []) {
            $this->addFlash('message', 'Votre panier est vide');
            return $this->redirectToRoute('app_home');
        }

        $user = $this->getUser();
        //Le panier n'est pas vide, on crée la commande
        $order = new Orders();

        // On remplit la commande
        $order->setUser($user);
        $order->setReference(uniqid());
        $shippingAddress = $shippingAddressesRepository->findOneBy([
            'user' => $user,
            'isMain' => true
        ]);
        $billingAddress = $billingAddressesRepository->findOneBy([
            'user' => $user,
            'isMain' => true
        ]);
        $order->setShippingAddress($shippingAddress);
        $order->setBillingAddress($billingAddress);


        // On parcourt le panier pour créer les détails de commande
        foreach ($cart as $item => $quantity) {
            if (is_int($item)){
                $orderDetails = new OrdersDetails();

                // On va chercher le produit
                $product = $productsRepository->find($item);

                $price = $product->getPrice();

                // On crée le détail de commande
                $orderDetails->setProducts($product);
                $orderDetails->setPrice($price);
                $orderDetails->setQuantity($quantity);

                $order->addOrdersDetail($orderDetails);

                //Au lieu de "$em->persist($orderDetails);", possibilité d'ajouter cascade: ['persist'] dans l'entité order :
                // #[ORM\OneToMany(mappedBy: 'orders', targetEntity: OrdersDetails::class, orphanRemoval: true, cascade: ['persist'])]
                // private $ordersDetails
                $em->persist($orderDetails);
            }

        }

        // On persiste et on flush
        $em->persist($order);
        $em->flush();

        $session->remove('cart');

        $this->addFlash('successMessageFlash', 'Commande passée avec succès');
        return $this->redirectToRoute('app_order_account');
    }
    #[Route('/cancel', name: 'cancel')]
    public function cancel(): Response
    {

        $this->addFlash('infoMessageFlash', 'La commande a été annulée');
        return $this->redirectToRoute('app_home');
    }
    #[Route('/ajouter', name: 'ajouter')]
    public function ajouter(): Response
    {

        dd('fgfgfg');
    }

}
