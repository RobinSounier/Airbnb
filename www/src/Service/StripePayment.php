<?php

namespace App\Service;

use Stripe\Stripe;

class StripePayment
{
    private \Stripe\StripeClient $stripeClient;

    public function __construct(
        private readonly string $stripeSecretKey,
        private readonly string $successUrl,
        private readonly string $cancelUrl
    ) {
        // Initialisation de la librairie Stripe avec la clé secrète.
        Stripe::setApiKey($this->stripeSecretKey);
    }

    /**
     * Crée une session Checkout pour un paiement unique.
     */
    public function createCheckoutSession(
        int $amount,
        string $currency,
        string $description,
        string $successUrl,
        string $cancelUrl
    ): string {
        try {
            // Créer la session de paiement
            $session = $this->stripeClient->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'unit_amount' => $amount, // L'unité de montant est en centimes (pour EUR/USD)
                        'product_data' => [
                            'name' => 'Réservation AirBnb',
                            'description' => $description,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
            ]);

            // Retourne l'ID de la session ou l'URL de redirection
            return $session->url;

        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Gérer l'erreur API
            throw new \Exception("Erreur Stripe : " . $e->getMessage());
        }
    }
}