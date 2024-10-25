<?php

/**
 * @package PayUmoneyProcessor
 * @author techvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 21-12-2022
 */


namespace App\Services\Gateways\PayUmoney;


use App\Services\Gateways\Gateway\Exceptions\{
    GatewayInitializeFailedException,
    PaymentFailedException
};
use App\Services\Gateways\Gateway\PaymentProcessor;
use Exception;




/**
 * @method array pay()
 */
class PayUmoneyProcessor extends PaymentProcessor
{
    protected $data;

    protected $payumoney;

    protected $baseurl;

    protected $accessToken;


    /**
     * Boot PayUmoney payment processor
     *
     * @param array $data
     *
     * @return void
     */
    protected function boot($data)
    {
        $this->data = $data;

        $this->paymentCurrency();

        if ($this->currency !== 'INR') {
            throw new GatewayInitializeFailedException(__('PayUMoney only supports Indian Rupee(INR)'));
        }

        $this->payumoney = $this->paymentMethodCredentials();

        if (!$this->payumoney->mode || !$this->payumoney->key || !$this->payumoney->salt) {
            throw new GatewayInitializeFailedException(__("PayUmoney initialize failed."));
        }

        $this->baseurl = ($this->payumoney->mode == 'production') ? 'https://secure.payu.in/_payment' : 'https://sandboxsecure.payu.in/_payment';

    }

    /**
     * Confirm payment for PayUmoney
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

            $this->validateInitiatePaymentRequest($data);

            $paymentData =  $this->setPaymentData();

            return [
                'data' => $paymentData,
            ];

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
        return "payumoney";
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
            'redirect_url' => 'required',
            'transaction_type' =>'required',
            'payment_type' => 'required'
        ];
        return $this->validateData($data, $rules);
    }

    public function setPaymentData()
    {
        $data = [
            'amount' => number_format((float) $this->data['amount'], 2, '.', ''),
            'mode' => $this->payumoney->mode,
            'key' => $this->payumoney->key,
            'salt' =>  $this->payumoney->salt,
            'email' => auth()->user()->email,
            'txnid' => unique_code(),
            'firstname' =>  auth()->user()->first_name,
            'productinfo' => $this->data['payment_type'],
            'service_provider' => 'payu_paisa',
            'surl' =>   url('gateway/payment-verify/payumoney').'?params='.$this->data['params'],
            'furl' => $this->data['cancel_url'],
            'baseurl'=>  $this->baseurl,
        ];


        $hashSequence = $data['key'] . '|' . $data['txnid'] . '|' . $data['amount'] . '|' . $data['productinfo'] . '|' . $data['firstname'] . '|' . $data['email'] . '|||||||||||' . $data['salt'];

        $data['hash'] = hash("sha512", $hashSequence);

        return $data;

    }

    public function verify($request)
    {
        if ($request->status == 'success') {

            $data = getPaymentParam($request->params);

            
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
    }


}
