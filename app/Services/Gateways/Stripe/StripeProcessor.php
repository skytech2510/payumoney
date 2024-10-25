<?php

/**
 * @package StripeProcessor
 * @author tehcvillage <support@techvill.org>
 * @contributor Md Abdur Rahaman Zihad <[zihad.techvill@gmail.com]>
 * @created 21-12-2022
 */


namespace App\Services\Gateways\Stripe;

use App\Services\Gateways\Gateway\Exceptions\{
    GatewayInitializeFailedException
};
use App\Services\Gateways\Gateway\PaymentProcessor;
use Exception;
use Stripe\StripeClient;


/**
 * @method array pay()
 */
class StripeProcessor extends PaymentProcessor
{
    protected $data;

    protected $stripe;

    /**
     * Initiate the stripe payment process
     *
     * @param array $data
     *
     * @return void
     */
    protected function pay(array $data) : array
    {
        $data['payment_method_id'] = Stripe;

        // Boot stripe payment initiator
        $this->boot($data);

        // create payment intent
        $response =  $this->createPaymentIntent(
            $data['totalAmount'],
            $this->currency,
        );

        if ($response['status'] == false) {
            throw new GatewayInitializeFailedException(__("Stripe initialize failed."));
        }

        return $response;
    }

    /**
     * Boot stripe payment processor
     *
     * @param array $data
     *
     * @return void
     */
    protected function boot($data)
    {
        $this->data = $data;

        $this->paymentCurrency();

        $this->stripe = $this->paymentMethodCredentials();

        if (!$this->stripe->secret_key) {
            throw new GatewayInitializeFailedException(__("Stripe initialize failed."));
        }
    }


    public function createPaymentIntent($amount, $currency) {
        try {
            $stripe = new StripeClient($this->stripe->secret_key);

            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => round($amount * $this->resolveFactor($currency)),
                'currency' => strtolower($currency),
                'payment_method_types' => ["card"],
            ]);

            return [
                'status' => true,
                'message' => __('success'),
                'publishableKey' => $this->stripe->publishable_key,
                'paymentIntent' => $paymentIntent->client_secret,
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get gateway alias name
     *
     * @return string
     */
    public function gateway(): string
    {
        return "stripe";
    }

    public function verify($request)
    {
        try {

            $data = getPaymentParam($request->params);
            $data['payment_method_id'] = Stripe;
            $this->setPaymentType($data['payment_type']);
            $this->boot($data);

            $stripe = new StripeClient($this->stripe->secret_key);
            $paymentIntent = $stripe->paymentIntents->retrieve(
                $request->payment_intent,
                []
            );

            if ($paymentIntent['status'] == 'succeeded') {

                $payment = callAPI(
                    'GET',
                    $data['redirectUrl'],
                    [
                        'params' => $request->params,
                        'execute' => 'api'
                    ]
                );

                $data ['transaction_id'] = $payment;

                return $data;
            }

            throw new GatewayInitializeFailedException(__("Stripe Payment failed."));


        } catch (Exception $e) {

            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }

    }




    /**
     * Method paymentView
     *
     * @return void
     */
    public function paymentView()
    {
        return 'gateways.'.$this->gateway();
    }


    public function resolveFactor($currency)
    {
        $zeroDecimalCurrencies = ['BIF', 'CLP', 'DJF', 'GNF', 'JPY', 'KMF', 'KRW', 'MGA', 'PYG', 'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF'];

        if (in_array(strtoupper($currency), $zeroDecimalCurrencies)) {
            return 1;
        }

        return 100;
    }

}
