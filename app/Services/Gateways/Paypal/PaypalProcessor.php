<?php

/**
 * @package PaypalProcessor
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 21-12-2022
 */


namespace App\Services\Gateways\Paypal;

use App\Services\Gateways\Gateway\Exceptions\{
    GatewayInitializeFailedException,
    PaymentFailedException
};
use App\Services\Gateways\Gateway\PaymentProcessor;
use Exception;
use Illuminate\Support\Facades\Http;




/**
 * @method array pay()
 */
class PaypalProcessor extends PaymentProcessor
{
    protected $data;

    protected $paypal;

    protected $baseurl;

    protected $accessToken;


    /**
     * Boot paypal payment processor
     *
     * @param array $data
     *
     * @return void
     */
    protected function boot($data)
    {
        $this->data = $data;

        $this->paymentCurrency();

        $this->paypal = $this->paymentMethodCredentials();

        if (!$this->paypal->client_id || !$this->paypal->client_secret || !$this->paypal->mode) {
            throw new GatewayInitializeFailedException(__("Paypal initialize failed."));
        }

        $this->baseurl = ($this->paypal->mode == 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        $this->accessToken = $this->getAccessToken();

    }


    /**
     * Confirm payment for Paypal
     *
     * @param array $data
     *
     * @return mixed
     *
     * @throws PaymentFailedException
     */
    public function pay(array $data): array
    {
        try {
            $this->boot($data);

            $this->validatePaymentRequest($data);

            isTransactionExist($data['uuid']);

            $order = $this->createOrder();


            session()->put('approvalId', $order->id);
            session()->put('accessToken', $this->accessToken);

            if (!isset($order->links)) {
                throw new Exception(__('Paypal initialize failed.'));
            }
            $orderLinks = collect($order->links);
            $approve = $orderLinks->where('rel', 'approve')->first();

            return [
                "action" => "success",
                "message" => __("Order Created."),
                "type" => 'paypal',
                "orderId" =>  $order->id,
                "href" => $approve->href
            ];

            return $approve;

        } catch (Exception $th) {
            throw new PaymentFailedException($th->getMessage());
        }
    }

    public function paymentView()
    {
        return 'gateways.'.$this->gateway();
    }


    /**
     * Get gateway alias name
     *
     * @return string
     */
    public function gateway(): string
    {
        return "paypal";
    }

    /**
     * Validate payment request
     *
     * @param array $data
     *
     * @return array
     */
    private function validatePaymentRequest($data)
    {
        $rules = [
            'amount' => 'required', 'numeric', 'min:5',
            'currency_id' => 'required', 'numeric',
            'payment_method_id' => 'required','exists:payment_methods,id',
        ];
        return $this->validateData($data, $rules);
    }


    /**
     * Validate initialization request
     *
     * @param array $data
     *
     * @return array
     */
    private function validateInitiatePaymentRequest($data)
    {
        $rules = [
            'amount' => 'required',
            'currency_id' => 'required',
            'payment_method_id' => 'required', 'exists:payment_methods,id',
            'redirectUrl' => 'required',
            'transaction_type' =>'required',
            'payment_type' => 'required'
        ];
        return $this->validateData($data, $rules);
    }

    public function initiateGateway($data)
    {
        $this->setPaymentType($data['payment_type']);

        $this->validateInitiatePaymentRequest($data);

        return view(('gateways.'.$this->gateway()), $data);
    }


    /**
     * Method authorizationString
     *
     * @return void
     */
    public function authorizationString()
    {
        return base64_encode($this->paypal->client_id . ":" . $this->paypal->client_secret);
    }

    /**
     * Method getAccessToken
     *
     * @return void
     */
    public function getAccessToken()
    {
        $url = $this->baseurl.'/v1/oauth2/token';

        $authorize = $this->authorizationString();

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => 'Basic '. $authorize
        ])
            ->asForm()
            ->post($url, [
                'grant_type' => 'client_credentials'
            ]);

        $response = json_decode($response);

        if (isset($response->error)) {
            throw new Exception($response->error_description);
        }

        return $response->access_token;

    }

    public function createOrder()
    {
        $url =  $this->baseurl.'/v2/checkout/orders';

        $response = Http::withHeaders(
            $this->getHeaders()
        )->post(
            $url,
            $this->getRequestBody()
        );

        return json_decode($response);
    }


    public function capturePayment($token, $accessToken)
    {
        $curl = curl_init();

        $url = $this->baseurl.'/v2/checkout/orders/'.$token.'/capture';

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Prefer: return=representation',
                'Authorization: Bearer '.$accessToken,
            ),
        ));

        $response = curl_exec($curl);

        return json_decode($response);
    }

    public function verify($request)
    {
        $accessToken = session('accessToken');
        $approvalId = session('approvalId');

        try {
            $data = getPaymentParam($request->params);
            $data['payment_method_id'] = Paypal;
            $this->setPaymentType($data['payment_type']);
            $this->boot($data);
            $paymentCapture = $this->capturePayment($approvalId, $accessToken);

            if (isset($paymentCapture->status) && ($paymentCapture->status == 'COMPLETED')) {

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

            throw new GatewayInitializeFailedException(__("Paypal Payment failed."));

        } catch (Exception $e) {
            throw new GatewayInitializeFailedException(__($e->getMessage()));
        }

    }


    public function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$this->accessToken
        ];

    }

    private function getRequestBody()
    {
        return [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => unique_code(),
                    'amount' => [
                        'currency_code' => $this->currency,
                        'value' => strval(round($this->data['amount'], 2)),
                    ],
                ],
            ],
            'application_context' => [
                'brand_name' => settings('name'),
                'return_url' => $this->data['verify_url'].'?params='.$this->data['params'],
                'cancel_url' => url('payment/fail'),
                'user_action' => 'PAY_NOW',
            ],
        ];
    }

}





