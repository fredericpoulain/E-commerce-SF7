<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Stripe\Checkout\Session;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;

class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index(SessionInterface $sessionInterface, ProductsRepository $productsRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
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
            'mode' => 'payment',
            'success_url' => 'http://127.0.0.1:8000/orders/add',
            'cancel_url' => 'http://127.0.0.1:8000/orders/cancel',
            //si notre application ne gère pas en amont les adresses de livraisons et facturation, on peut demander à stripe de récupérer ces informations.
            //Ensuite on pourra récupérer ces adresses pour gérer la livraison et la facture
//            'billing_address_collection' => 'required',
//            'shipping_address_collection' => [
//                'allowed_countries' => ['FR'],
//            ],
        'line_items' => $arrayLineItems
        ]);
        $cart['idSessionStripe'] = $sessionStripe->id;
        $sessionInterface->set('cart', $cart);
       //rediriger...
        return $this->redirect($sessionStripe->url);
    }

    #[Route('/stripe/webhook', name: 'app_stripe_webhook')]
    public function stripeWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sig_header = $request->headers->get('Stripe-Signature');
        $endpoint_secret = $this->getParameter('stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            return new Response('Invalid payload', 400);
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return new Response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.payment_failed':
                $paymentIntent = $event->data->object; // contains a \Stripe\PaymentIntent
                $logMessage = sprintf(
                    "Payment failed: %s. Reason: %s\n",
                    $paymentIntent->id,
                    $paymentIntent->last_payment_error ? $paymentIntent->last_payment_error->message : 'Unknown error'
                );
                //Ici, c'est un simple test pour voir le si l'event "payment_intent.payment_failed" a bien été capturé.
                // (on peut imaginer dans une autre application mettre à jour le statut de la commande dans la base de données.)
                file_put_contents($this->getParameter('kernel.project_dir').'/payment-failed.log', $logMessage, FILE_APPEND);

                break;
            // ... handle other event types
            default:
                return new Response('Received unknown event type', 400);
        }

        return new Response('Received webhook', 200);
    }
}
