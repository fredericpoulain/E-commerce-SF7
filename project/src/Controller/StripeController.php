<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Repository\OrdersRepository;
use App\Repository\OrderStatusRepository;
use App\Repository\ProductsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class StripeController extends AbstractController
{
    #[Route('/stripe/{id}', name: 'app_stripe')]
    public function index($id, OrdersRepository $ordersRepository, SessionInterface $sessionInterface, ProductsRepository $productsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $orders = $ordersRepository->find($id);
        $statusName = $orders->getOrderStatus()->getStatusName();
        if ($statusName === 'pendingPayment' || $statusName  === 'paymentFailed') {

            $secretKeyStripe = $this->getParameter('app.secreteKeyStripe');
            \Stripe\Stripe::setApiKey($secretKeyStripe);
            \Stripe\Stripe::setApiVersion('2024-06-20');
            $cart = $sessionInterface->get('cart', []);
            $arrayLineItems = [];
            foreach ($cart as $item => $quantity) {
                if (is_int($item)){
                    $product = $productsRepository->find($item);
                    $price = $product->getPrice();
                    $name = $product->getName();

                    $arrayLineItems[] = [
                        'quantity' => $quantity,
                        'price_data'=> [
                            'currency' => 'EUR',
                            'product_data' => [
                                'name' => $name,
                            ],
                            'unit_amount' => $price,
                        ]
                    ];
                }

            }

            $sessionStripe = Session::create([
                //mode ici ce sera un paiment. si on choisi subscription, c'est pour démarrer un abonnement

                //si notre application ne gère pas en amont les adresses de livraisons et facturation, on peut demander à stripe de récupérer ces informations.
                //Ensuite on pourra récupérer ces adresses pour gérer la livraison et la facture
//            'billing_address_collection' => 'required',
//            'shipping_address_collection' => [
//                'allowed_countries' => ['FR'],
//            ],
                'payment_method_types' => ['card'],
                'line_items' => [$arrayLineItems],
                'mode' => 'payment',
                'success_url' => 'http://127.0.0.1:8000/commandes',
                'cancel_url' => 'http://127.0.0.1:8000/orders/cancel',
                'payment_intent_data' => [
                    'metadata' => [
                        'orderId' => $orders->getId(),
                    ],
                ],
            ]);
            $cart['idSessionStripe'] = $sessionStripe->id;
            $sessionInterface->set('cart', $cart);
            //rediriger...
            return $this->redirect($sessionStripe->url);
        }
        $this->addFlash('infoMessageFlash', 'Commande déjà payée');
        return $this->redirectToRoute('app_order_account');
    }

    #[Route('/webhookStripe', name: 'app_stripe_webhook')]
    public function stripeWebhook(
        Request $request,
        OrderStatusRepository $orderStatusRepository,
        OrdersRepository $ordersRepository,
        EntityManagerInterface $entityManager,
    ): Response
    {

//        $this->denyAccessUnlessGranted('ROLE_USER');
        $secretKeyStripe = $this->getParameter('app.secreteKeyStripe');
        $endpoint_secret = $this->getParameter('stripe.webhook_secret');

        new \Stripe\StripeClient($secretKeyStripe);
        \Stripe\Stripe::setApiKey($secretKeyStripe);
        \Stripe\Stripe::setApiVersion('2024-06-20');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;
//        $user = $this->getUser();

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }
        $paymentIntent = $event->data->object;
        $metadata = $paymentIntent->metadata;
        $orderId = $metadata->orderId ?? 'unknown';
        $order = $ordersRepository->find($orderId);
        switch ($event->type) {
//            case 'payment_intent.payment_failed':
            case 'payment_intent.payment_failed':

                $paymentFailedtStatus = $orderStatusRepository->findOneBy(['statusName' => 'paymentFailed']);
                if (!$paymentFailedtStatus) {
                    throw new \Exception("Le statut 'paymentFailed' n'existe pas.");
                }
                $order->setOrderStatus($paymentFailedtStatus);
                break;
            case 'payment_intent.succeeded':

                $paymentSuccessStatus = $orderStatusRepository->findOneBy(['statusName' => 'paymentSuccess']);
                if (!$paymentSuccessStatus) {
                    throw new \Exception("Le statut 'paymentSuccess' n'existe pas.");
                }
                $order->setOrderStatus($paymentSuccessStatus);
                break;
            default:
                echo 'Received unknown event type ' . $event->type;
        }
        $entityManager->persist($order);
        $entityManager->flush();
        return new Response(http_response_code(200));
    }
}