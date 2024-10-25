<?php

/**
 * @package CoinPaymentsProcessor
 * @author tehcvillage <support@techvill.org>
 * @contributor Ashraful Rasel <[ashraful.techvill@gmail.com]>
 * @created 10-10-2023
 */

namespace App\Services\Gateways\Coinpayments;

use App\Models\{CoinpaymentLogTrx,
    Merchant,
    Transaction,
    Wallet
};

use App\Repositories\CoinPaymentRepository;

use App\Services\Gateways\Gateway\{
    Exceptions\GatewayInitializeFailedException,
    Exceptions\PaymentFailedException,
    PaymentProcessor
};
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * @method array pay()
 */
class CoinpaymentsProcessor extends PaymentProcessor
{
    protected $data;

    protected $methodData;

    protected $coinPayment;

    /**
     * Boot CoinPayment payment processor
     *
     * @param array $data
     *
     * @return void
     */
    protected function boot($data)
    {
        $this->data = $data;

        $this->paymentCurrency();

        $this->methodData = $this->paymentMethodCredentials();

        if (!$this->methodData->private_key || !$this->methodData->public_key) {
            throw new GatewayInitializeFailedException(__("CoinPayments initialize failed."));
        }

        $this->coinPayment = new CoinPaymentRepository();

        $this->coinPayment->Setup($this->methodData->private_key, $this->methodData->public_key);
    }

    /**
     * Method initiateGateway
     *
     * @param $data $data
     *
     * @return void
     */
    public function initiateGateway($data)
    {
        $this->setPaymentType($data['payment_type']);

        $this->validateInitiatePaymentRequest($data);

        $gatewayData = (array_merge($data, ['total_amount' => $data['total'], 'payment_method_id' => $data['payment_method']]));

        if ($data['currencyType'] == 'crypto') {
            return $this->initiatePayment($gatewayData);
        }

        $data = array_merge($data, $this->initiatePayment($gatewayData));

        return view(('gateways.' . $this->gateway()), $data);
    }

    /**
     * Initiate the CoinPayment payment process
     *
     * @param array $data
     *
     * @return void
     */
    protected function initiatePayment(array $data)
    {
        $this->validateInitializationRequest($data);

        $this->boot($data);

        if ($data['currencyType'] == 'fiat') {
            return $this->getAcceptedCryptoCoin();
        }

        if ($data['currencyType'] == 'crypto') {
            return $this->createCoinPaymentTransaction();
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
        isTransactionExist($data['uuid']);

        $gatewayData = (array_merge($data, ['total_amount' => $data['total'], 'payment_method_id' => $data['payment_method']]));

        $this->validatePaymentConfirmRequest($gatewayData);

        $this->boot($gatewayData);

        return [
            'type' => 'coinpayments',
            'view' => $this->createCoinPaymentTransaction(),
        ];
    }

    /**
     * Method getAcceptedCryptoCoin
     *
     * @return void
     */
    protected function getAcceptedCryptoCoin()
    {

        $rates = $this->coinPayment->GetRates(0);

        if ($rates['error'] != 'ok') {
           throw new Exception($rates['error']);
        }

        $rates = $rates['result'];

        $currencyRate = $rates[$this->currency]['rate_btc'];
        $rateAmount = $currencyRate * $this->data['total'];
        $currencyList = getFormatedCurrencyList($rates, $rateAmount);

        return [
            'coins' => $currencyList['coins'],
            'coin_accept' => $currencyList['coins_accept'],
            'encoded_coin_accept' => json_encode($currencyList['coins_accept']),
            'fiat' => $currencyList['fiat'],
            'aliases' => $currencyList['aliases'],
        ];
    }

    protected function createCoinPaymentTransaction()
    {
        $getPaymentParam = getPaymentParam($this->data['params']);

        $rates = $this->coinPayment->GetRates(0);

        if ($rates['error'] != 'ok' ) {
           throw new Exception($rates['error']);
        }

        $rates = $rates['result'];

        $currencyRate = $rates[$this->currency]['rate_btc'];
        $rateAmount = $currencyRate * $this->data['total'];
        $currencyList = getFormatedCurrencyList($rates, $rateAmount);
        $acceptedCoin = $currencyList['coins_accept'];
        $acceptedCoinIso = array_column($acceptedCoin, 'iso');

        $selectedCurrency = (isset($this->data['selected_coin'])) ? $this->data['selected_coin'] : $this->currency;

        if (!in_array($selectedCurrency, $acceptedCoinIso)) {
            throw new GatewayInitializeFailedException(__("CoinPayments Payment failed."));
        }

        $transactionData = [
            'amount' => $this->data['totalAmount'],
            'currency1' => $this->currency,
            'currency2' => $selectedCurrency,
            'buyer_email' => auth()->check() ? auth()->user()->email : null,
            'buyer_name' => auth()->check() ? getColumnValue(auth()->user()) : null,
            'item_name' => ($this->data['transaction_type'] == Deposit) ? __('Deposit via coinpayment') : __('Payment via coinpayment'),
            'custom' => $this->data['uuid'],
            'ipn_url' => url("gateway/payment-verify/coinpayments"),
        ];

        $makeTransaction = $this->coinPayment->CreateTransaction($transactionData);

        if ($makeTransaction['error'] !== 'ok') {
            throw new GatewayInitializeFailedException(__("CoinPayments Payment failed."));
        }

        switch ($this->data['transaction_type']) {
            case Deposit:
                $type = 'deposit';
                break;
            case Payment_Sent:
            case Payment_Received:
                $type = 'merchant_payment';
                break;
            default:
                // Handle the default case here, if needed
                break;
        }

        if ( isActive('CryptoExchange') && ($this->data['transaction_type'] == Crypto_Buy)) {
            $type = 'crypto_exchange';
        }

        if (isActive('Investment') && $this->data['transaction_type'] == Investment) {
            $type = 'investment';
        }

        $payload = ['type' => $type, 'currency' => $this->currency];
        $makeTransaction['payload'] = $payload;
        $transactionInfo = $this->getCoinPaymentTransactionInfo($makeTransaction['result']['txn_id']);

        if ($transactionInfo['error'] !== 'ok') {
            throw new GatewayInitializeFailedException(__("CoinPayments Payment failed."));
        }

        if ($transactionInfo['error'] === 'ok') {

            $response = $this->createTransaction();
            $data = json_decode($response, true);

            $transactionId = isset($data['data']['transaction']) ? $data['data']['transaction']['id'] : null;

            $transactionInfoData = $transactionInfo['result'];

            $coinPaymentLogTrx = [
                'payment_id' => $makeTransaction['result']['txn_id'],
                'payment_address' => $transactionInfoData['payment_address'],
                'coin' => $transactionInfoData['coin'],
                'fiat' => $payload['currency'],
                'status_text' => $transactionInfoData['status_text'],
                'status' => $transactionInfoData['status'],
                'payment_created_at' => date('Y-m-d H:i:s', $transactionInfoData['time_created']),
                'expired' => date('Y-m-d H:i:s', $transactionInfoData['time_expires']),
                'amount' => $transactionInfoData['amountf'],
                'confirms_needed' => empty($makeTransaction['result']['confirms_needed']) ? 0 : $makeTransaction['result']['confirms_needed'],
                'qrcode_url' => empty($makeTransaction['result']['qrcode_url']) ? '' : $makeTransaction['result']['qrcode_url'],
                'status_url' => empty($makeTransaction['result']['status_url']) ? '' : $makeTransaction['result']['status_url'],
            ];

            if ($this->data['transaction_type'] == Payment_Sent) {
                $coinPaymentLogTrx['merchant_id'] = $getPaymentParam['merchant_id'];
            }

            $payload['transaction_id'] = $transactionId;
            $payload['uuid'] = $this->data['uuid'];
            $payload['receivedf'] = $transactionInfoData['receivedf'];
            $payload['time_expires'] = $transactionInfoData['time_expires'];
            $payload = json_encode($payload);

            $coinPaymentLogTrx['payload'] = $payload;

            auth()->check() ? auth()->user()->coinpayment_transactions()->create($coinPaymentLogTrx) : CoinpaymentLogTrx::create($coinPaymentLogTrx);

            Session::put('transactionDetails', $makeTransaction);
            Session::put('transactionInfo', $transactionInfo);

            return redirect()->route('coinpayment.summery');

        }
    }

    /**
     * Get gateway alias name
     *
     * @return string
     */
    public function gateway(): string
    {
        return "coinpayments";
    }

    /**
     * Validate payment confirm request
     *
     * @param array $data
     *
     * @return array
     */
    private function validatePaymentConfirmRequest($data)
    {
        $rules = [
            'payment_method' => 'required',
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
    private function validateInitializationRequest($data)
    {
        $rules = [
            'amount' => 'required|numeric',
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
            'amount' => ['required'],
            'currency_id' => ['required'],
            'payment_method' => ['required', 'exists:payment_methods,id'],
            'redirectUrl' => ['required'],
            'transaction_type' => ['required'],
            'payment_type' => ['required'],
        ];
        return $this->validateData($data, $rules);
    }

    public function paymentView()
    {
        return 'gateways.' . $this->gateway();
    }

    public function createTransaction()
    {
        $payment = callAPI(
            'GET',
            $this->data['redirectUrl'],
            [
                'params' => $this->data['params'],
                'execute' => 'api',
                'uuid' => $this->data['uuid'],
            ]
        );

        return $payment;
    }

    public function getCoinPaymentTransactionInfo($txn_id)
    {
        return $this->coinPayment->getTransactionInfo(['txid' => $txn_id]);
    }

    public function verify($request)
    {
        $responseArray = $request->all();
        return $this->coinpaymentsCheckStatus($responseArray);
    }

    public function coinpaymentsCheckStatus($responseArray)
    {
        if (htmlspecialchars($responseArray['ipn_type']) == 'api') {

            $txn_id = htmlspecialchars($responseArray['txn_id']);
            $custom_uuid = htmlspecialchars($responseArray['custom']);
            $status = htmlspecialchars(intval($responseArray['status']));
            $status_text = htmlspecialchars($responseArray['status_text']);

            $coinLog = CoinpaymentLogTrx::where(['status' => 0, 'payment_id' => $txn_id])->where('payload', 'LIKE', '%' . $custom_uuid . '%')->first(['id', 'payload', 'payment_id', 'status_text', 'status', 'confirmation_at']);

            $coinLogResponse = isset($coinLog->payload) ? json_decode($coinLog->payload) : null;

            $data = getPaymentParam($coinLogResponse->uuid);


            if (isset($coinLogResponse->type)) {

                $modelName = Str::studly(str_replace('_', ' ', $coinLogResponse->type));
                if (in_array($modelName, ['Deposit', 'MerchantPayment', 'CryptoExchange', 'Invest'])) {

                    $model = 'App\\Models\\' . $modelName;

                    if ($modelName == 'CryptoExchange') {
                        $model = 'Modules\\CryptoExchange\\Entities' . $modelName;
                    }

                    if ($modelName == 'Invest') {
                        $model = 'Modules\\Investment\\Entities' . $modelName;
                    }

                    $payment = $model::where(['uuid' => $coinLogResponse->uuid])->first();

                }

                if (!empty($payment)) {

                    try {
                        DB::beginTransaction();

                        $coinLog->status_text = $status_text;
                        $coinLog->status = $status;
                        $coinLog->confirmation_at = date('Y-m-d H:i:s', time());
                        $coinLog->save();

                        $transaction = Transaction::where(['uuid' => $coinLogResponse->uuid])->first(['id', 'user_id', 'currency_id', 'subtotal', 'transaction_type_id', 'payment_status', 'status']);

                        $transactionStatus = ((int) $status === 100) ? "Success" : "Blocked";

                        if (isActive('CryptoExchange')) {
                            if ($transaction->transaction_type_id == Crypto_Buy) {
                                $transactionStatus = ((int) $status === 100) ? "Pending" : "Blocked";
                            }
                        }

                        if (isActive('Investment') ) {
                            if ($transaction->transaction_type_id == Investment) {
                                $invest_status = settings('invest_start_on_admin_approval') == 'Yes' ? 'Pending' : 'Active';
                                $transactionStatus = ((int) $status === 100) ? $invest_status : "Blocked";
                            }
                        }

                        if (!empty($transaction)) {
                            $transaction->payment_status = ((int) $status === 100) ? "Success" : "Blocked";
                            $transaction->status = $transactionStatus;
                            $transaction->save();
                        }

                        if (!empty($payment)) {
                            $payment->status = ((int) $status === 100) ? $transactionStatus : "Blocked";
                            $payment->save();
                        }

                        if ($status == 100 && $status_text == 'Complete') {

                            if ($transaction->transaction_type_id == Deposit || $transaction->transaction_type_id == Payment_Received) {

                                $userId = isset($transaction->merchant_id)
                                ? Merchant::find($transaction->merchant_id)->user_id
                                : $transaction->user_id;

                                $wallet = Wallet::where(['user_id' => $userId, 'currency_id' => $transaction->currency_id])->first(['id', 'balance']);
                                if (empty($wallet)) {
                                    $wallet = Wallet::createWallet($userId, $transaction->currency_id);
                                }
                                $wallet->balance = ($wallet->balance + $transaction->subtotal);
                                $wallet->save();

                            }
                        }
                        DB::commit();

                        $data['payment_method_id'] = Coinpayments;
                        $data ['transaction_id'] = $transaction->id;
                        return $data;


                    } catch (Exception $e) {
                        DB::rollBack();
                        throw new Exception($e->getMessage());
                    }
                }
            }
        }
    }
}
