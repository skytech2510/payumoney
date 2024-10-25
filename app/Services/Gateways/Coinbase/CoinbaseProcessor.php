<?php

/**
 * @package CoinbaseProcessor
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 03-08-2023
 */

namespace App\Services\Gateways\Coinbase;

use App\Models\{
    Deposit,
    Merchant,
    MerchantPayment,
    Transaction,
    Wallet
};

use App\Services\Gateways\Gateway\{
    Exceptions\GatewayInitializeFailedException,
    Exceptions\PaymentFailedException,
    PaymentProcessor
};
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class CoinbaseProcessor extends PaymentProcessor
{
    protected $data;

    protected $coinbase;

    protected $baseurl;

    protected $uniqid;

    /**
     * Boot coinbase payment processor
     *
     * @param array $data
     *
     * @return void
     */
    protected function boot($data)
    {
        $this->data = $data;

        $this->paymentCurrency();

        $this->coinbase = $this->paymentMethodCredentials();

        if (!$this->coinbase->api_key) {
            throw new GatewayInitializeFailedException(__("coinbase initialize failed."));
        }

        $this->uniqid = $data['uuid'];

        $this->baseurl = 'https://api.commerce.coinbase.com/charges';
    }

    /**
     * Confirm payment for coinbase
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

            isTransactionExist($data['uuid']);

            $charge = $this->createCharge();

            if (!isset($charge['data']['hosted_url'])) {
                throw new Exception(__('Coinbase payment failed.'));
            }

            $this->createTransaction();

            return [
                "action" => "success",
                "message" => __("Charge Created."),
                "type" => 'coinbase',
                "href" => $charge['data']['hosted_url'],
            ];
        } catch (Exception $th) {
            throw new PaymentFailedException($th->getMessage());
        }
    }

    public function paymentView()
    {
        return 'gateways.' . $this->gateway();
    }

    /**
     * Get gateway alias name
     *
     * @return string
     */
    public function gateway(): string
    {
        return "coinbase";
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
            'transaction_type' => 'required',
            'payment_type' => 'required',
            'uuid' => 'required',
        ];
        return $this->validateData($data, $rules);
    }

    public function createCharge()
    {
        $response = Http::withHeaders(
            $this->getHeaders()
        )->post(
            $this->baseurl,
            $this->getRequestBody()
        );

        $response = json_decode($response, true);

        return $response;
    }

    public function getHeaders()
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'X-CC-Version' => '2018-03-22',
            'X-CC-Api-Key' => $this->coinbase->api_key,
        ];
    }

    private function getRequestBody()
    {
        return [
            'pricing_type' => 'fixed_price',
            'local_price' => [
                'currency' => $this->currency,
                'amount' => $this->data['amount'],
            ],
            'redirect_url' => url('gateway/payment-verify/coinbase?status=pending&uid=' . $this->uniqid),
            'cancel_url' => url('gateway/payment-cancel/coinbase?status=cancel&uid=' . $this->uniqid),
            'metadata' => [
                'customer_id' => Auth::check() ? auth()->id() : '',
                'customer_name' => Auth::check() ? auth()->user()->name : '',
            ],
            'name' => settings('name'),
            'description' => $this->data['payment_type'],
        ];
    }

    public function createTransaction()
    {
        $payment = callAPI(
            'GET',
            $this->data['redirect_url'],
            [
                'params' => $this->data['params'],
                'execute' => 'api',
                'uuid' => $this->uniqid,
            ]
        );

        return $payment;
    }

    public function verify($request)
    {

        try {
            $this->uniqid = $request->uid;
            $data = getPaymentParam($request->uid);
            $response = $request->getContent();
            $response = json_decode($response, true);
            if (isset($response['event']['type']) && isset($response['event']['data']['code']) && $response['event']['type'] == "charge:confirmed") {
                $transaction = $this->transactionUpdate();
                $data['payment_method_id'] = Coinbase;
                $data ['transaction_id'] = $transaction->id;
                return $data;
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function callBack($request)
    {
        $status = $request->status;
        $this->uniqid = $request->uid;
        if (!empty($this->uniqid) && ($status == 'cancel')) {
            $this->transactionUpdate('Blocked');
        }
    }

    public function transactionUpdate($status = 'Success')
    {
        $uid = $this->uniqid;

        $transaction = Transaction::where(['uuid' => $uid, 'status' => 'Pending'])
            ->first(['id', 'uuid', 'status', 'user_id', 'currency_id', 'subtotal', 'transaction_type_id', 'merchant_id', 'payment_status']);


        if (empty($transaction)) {
            throw new Exception(__('Transaction not found'));
        }

        $transactionArray = ['status' => $status, 'payment_status' => $status];

        if ($transaction->transaction_type_id == Deposit) {

            Deposit::where(['uuid' => $uid])->update(['status' => $status]);
        }

        if ($transaction->transaction_type_id == Payment_Received) {

            MerchantPayment::where(['uuid' => $uid])->update(['status' => $status]);
        }

        if (isActive('Investment')) {

            if ($transaction->transaction_type_id == Investment) {

                $invest_status = settings('invest_start_on_admin_approval') == 'Yes' ? 'Pending' : 'Active';

                $transactionArray = ['status' => $invest_status, 'payment_status' => $status];

                \Modules\Investment\Entities\Invest::where(['uuid' => $uid])->update(['status' => $invest_status]);
            }

        }

        if (isActive('CryptoExchange')) {

            if ($transaction->transaction_type_id == Crypto_Buy) {

                $transactionArray = ['payment_status' => $status];
            }
        }

        $transaction->update($transactionArray);

        if ($status == 'Success') {

            if ($transaction->transaction_type_id == Deposit || $transaction->transaction_type_id == Payment_Received) {

                $walletUserId = isset($transaction->merchant_id)
                ? Merchant::find($transaction->merchant_id)->user_id
                : $transaction->user_id;

                $wallet = Wallet::where(['user_id' => $walletUserId, 'currency_id' => $transaction->currency_id])->first();
                if (empty($wallet)) {
                    $wallet = Wallet::createWallet($walletUserId, $transaction->currency_id);
                }
                $wallet->balance = $wallet->balance + $transaction->subtotal;
                $wallet->save();
            }
        }

        return $transaction;
    }
}
