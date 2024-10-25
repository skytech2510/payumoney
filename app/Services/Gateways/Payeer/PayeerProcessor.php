<?php

/**
 * @package PayeerProcessor
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 01-08-2023
 */


namespace App\Services\Gateways\Payeer;

use App\Services\Gateways\Gateway\Exceptions\{
    GatewayInitializeFailedException,
    PaymentFailedException
};
use App\Services\Gateways\Gateway\PaymentProcessor;
use Exception;



/**
 * @method array pay()
 */
class PayeerProcessor extends PaymentProcessor
{
    protected $data;

    protected $payeer;

    protected $baseurl;

    protected $accessToken;

    protected $uniqid;


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

        $this->uniqid = $this->data['uuid'];

        $this->payeer = $this->paymentMethodCredentials();

        if (!$this->payeer->merchant_id || !$this->payeer->secret_key || !$this->payeer->encryption_key || !$this->payeer->merchant_domain
        ) {
            throw new GatewayInitializeFailedException(__("Payeer initialize failed."));
        }

    }


    /**
     * Confirm payment for stripe
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
            throw new PaymentFailedException($th->getMessage(), ["response" => $response ?? null]);
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
        return "payeer";
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
            'payment_type' => 'required',
            'uuid' => 'required'
        ];
        return $this->validateData($data, $rules);
    }

    public function setPaymentData()
    {

        $arHash[]  =  $this->payeer->secret_key;
        $sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));
        $order_id = otpCode6();

        $arParams       = array(
            'return_url' =>    url('gateway/payment-verify/payeer').'?params='.$this->data['params'],
            'success_url' =>   url('gateway/payment-verify/payeer').'?params='.$this->data['params'],
            'status_url'  =>   url('gateway/payment-verify/payeer').'?params='.$this->data['params'],
            'fail_url'    =>   url('gateway/payment-cancel/payeer'),
        );


        $cipher                = 'AES-256-CBC';
        $key                   = md5($this->payeer->encryption_key . $order_id);
        $m_params              = @urlencode(base64_encode(openssl_encrypt(json_encode($arParams), $cipher, $key, OPENSSL_RAW_DATA)));

        // Prepare the request data as an array
        $data = array(
            'm_shop' => $this->payeer->merchant_id,
            'm_orderid' => $order_id,
            'm_amount' => $this->data['amount'],
            'm_curr' => $this->currency,
            'm_desc' => $this->data['payment_type'],
            'm_sign' => $sign_hash,
            'form' => array(
                'ps' => '2609',
                'curr[2609]' => $this->currency,
            ),
            'm_params' => $m_params,
            'm_cipher_method' => 'AES-256-CBC',
            'form_currency_code' => $this->currency
        );

        return $data;

    }

    public function verify($request)
    {
        try {
            $data = getPaymentParam($request->params);
            $data['payment_method_id'] = Payeer;
            $this->setPaymentType($data['payment_type']);
            $this->boot($data);

            if (isset($request['m_operation_id']) && isset($request['m_sign'])) {

                $arHash = array(
                    $request['m_operation_id'],
                    $request['m_operation_ps'],
                    $request['m_operation_date'],
                    $request['m_operation_pay_date'],
                    $request['m_shop'],
                    $request['m_orderid'],
                    $request['m_amount'],
                    $request['m_curr'],
                    $request['m_desc'],
                    $request['m_status'],
                );

                //additional parameters
                if (isset($request['m_params'])) {
                    $arHash[] = $request['m_params'];
                }

                $arHash[]  = $this->payeer->secret_key;
                $sign_hash = strtoupper(hash('sha256', implode(':', $arHash)));

                if ($request['m_sign'] == $sign_hash && $request['m_status'] == 'success') {


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
        } catch (Exception $e) {
            
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    public function callBack($request)
    {
        $status = $request->status;
        $this->uniqid = $request->uid;
        if (!empty( $this->uniqid) && ($status == 'cancel')) {
            $this->transactionUpdate('Blocked');
        }
    }

}





