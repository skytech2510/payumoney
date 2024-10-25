<?php
namespace App\Repositories;

use Exception;
class StripeRepository
{
    protected $secretKey  = '';
    protected $amount     = 0;
    protected $currency   = 'usd';
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;

    public function __construct()
    {

    }
    public function makePayment($secretKey, $amount, $currency, $cardNumber, $month, $year, $cvc, $requestType="ajaxCall")
    {
        $data = [];
        $data['status']        = $this->unauthorisedStatus;
        $data['message']       = "fail";
        try {
            $stripe = new \Stripe\StripeClient($secretKey);
            $response = $stripe->paymentIntents->create([
                'amount' => $amount * 100,
                'currency' => $currency,
            ]);
            $data['paymentIntendId'] = $response->id;
            $stripe = new \Stripe\StripeClient($secretKey);
            $paymentMethod = $stripe->paymentMethods->create([
                'type' => 'card',
                'card' => [
                    'number'    => $cardNumber,
                    'exp_month' => $month,
                    'exp_year'  => $year,
                    'cvc'       => $cvc,
                ],
            ]);
            $data['paymentMethodId'] = $paymentMethod->id;
            $data['status']  = $this->successStatus;
            $data['message'] = "Success";
        } catch (Exception $e) {
            $data['status']  = $this->unauthorisedStatus;
            $data['message'] = $e->getMessage();
        }
        return response()->json($data);
    }
    
    public function paymentConfirm($secretKey, $paymentIntendId, $paymentMethodId)
    {
        $data = [];
        $data['status']        = $this->unauthorisedStatus;
        $data['message']       = "fail";
        try {
            $stripe = new \Stripe\StripeClient($secretKey);
            $data['paymentIntent']  = $stripe->paymentIntents->confirm(
                $paymentIntendId, 
                ['payment_method' => $paymentMethodId]);

            $data['status'] = $this->successStatus;
            if (isset($data['paymentIntent']->status) && $data['paymentIntent']->status != "succeeded") {
                $data['message']   = $data['paymentIntent']->message;
                if ($data['paymentIntent']->status == "requires_action") {
                    $data['message'] = __("3DS cards are not supported");
                }
                $data['status']  = $this->unauthorisedStatus;
            } else {
                $data['id']     = $data['paymentIntent']->id;
            }
        } catch (Exception $e) {
            $data['status']  = $this->unauthorisedStatus;
            $data['message'] = $e->getMessage();
        }
        return response()->json($data);
    }
}
