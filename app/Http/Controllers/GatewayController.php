<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Common;
use App\Models\Parameter;
use App\Services\Gateways\Gateway\GatewayHandler;
use App\Services\Gateways\Gateway\PaymentProcessor;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GatewayController extends Controller
{
    protected $helper;
    public $successStatus = 200;
    public $unprocessedStatus = 422;

    public function __construct()
    {
        $this->helper = new Common();

        if (!request('gateway')) {
            throw new Exception(__('Payment parameter isn\'t available'));
        }

        GatewayHandler::gatewayIssue(request("gateway"));

    }

    /**
     * Method pay
     * initiate payment processor
     * return payment gateway view
     *
     * @return void
     */
    public function pay(PaymentProcessor $processor)
    {
        try {

            if (!isset(request()->params)) {
                throw new Exception(__('Payment parameter isn\'t available'));
            }

            $data = getPaymentParam(request()->params);

            $data['params'] = request()->params;


            $this->validateData($data);

            if ($data['payment_method'] == Coinpayments) {

                return $processor->initiateGateway($data);
            }

            if ($data['payment_method'] == Stripe) {
                $processor->setPaymentType($data['payment_type']);
                $paymentIntent = $processor->pay($data);
                $data['publishableKey'] = $paymentIntent['publishableKey'];
                $data['paymentIntent'] = $paymentIntent['paymentIntent'];
            }

            return view($processor->paymentView(), $data);

        } catch (Exception $exception) {
            $this->helper->one_time_message('error', __($exception->getMessage()));
            return redirect('payment/fail');
        }
    }

    /**
     * Method confirmPayment
     *
     * @param Request $request
     * @param PaymentProcessor
     *
     * Execute the payment to the gateway
     *
     * @return void
     */
    public function confirmPayment(Request $request, PaymentProcessor $processor)
    {
        try {
            $processor->setPaymentType($request->payment_type);

            $response = $processor->pay(array_merge($request->all(), ['total_amount' => request()->amount]));

            if (!$request->ajax()) {

                if ($response['type'] == 'paypal' || $response['type'] == 'coinbase' ) {
                    return redirect($response['href']);
                }

                if ($response['type'] == 'coinpayments') {
                    return $response['view'];
                }

                if ($response['type'] == 'mts') {
                    return redirect($response['redirect_url'] . '?params=' . $request->params . '&user=' . $response['user']);
                }

                return redirect($response['redirect_url'] . '?params=' . $request->params . '&attachment=' . $response['attachment'] . '&bank=' . $response['bank']);
            }

            return response()->json([
                'data' => $response,
                'status' => $this->successStatus,
            ]);

        } catch (Exception $exception) {

            if (!$request->ajax()) {
                $this->helper->one_time_message('error', $exception->getMessage());
                return redirect('payment/fail');
            }

            return response()->json([
                'status' => $this->unprocessedStatus,
                'message' => $exception->getMessage(),
            ]);
        }

    }

    /**
     * Validate data against rules
     *
     * @param array $data
     *
     * @return array
     *
     * @throws Exception
     */
    public function validateData($data)
    {
        // Thess fields are required to implement a payment gateway
        $rules = [
            'currency_id' => 'required',
            'currencyCode' => 'required',
            'total' => 'required', // After calculating all fees (The amount which will be deduct from gateway account)
            'transaction_type' => 'required', // Deposit, Payment_Sent & other on demand module transaction type
            'payment_type' => 'required', // To calculate fees limit need to provide payment type, if transaction type is Deposit payment type will be "deposit"
            'redirectUrl' => 'required', // After payment done via gateway will execute the url to process the transaction
            'gateway' => 'required', // Payment method name
            'payment_method' => 'required', // Payment Method id
            'banks' => 'required_if:payment_method,' . Bank,
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            //  Return first error message
            throw new Exception($errors[0]);
        }

        return $validator->validated();
    }

    public function verify(Request $request, PaymentProcessor $processor)
    {
        try {

            $response = $processor->verify($request);

            Parameter::where('unique_code', $request->params)->delete();

            setPaymentData($response);

            if ( isActive('CryptoExchange') && ($response['transaction_type'] == Crypto_Buy)) {
                return redirect()->route('guest.crypto_exchange.view');
            }

            if (isActive('Investment') && $response['transaction_type'] == Investment) {
                return redirect()->route('user.invest.success', $response['transaction_id']);
            }

            switch ($response['transaction_type']) {
                case Deposit:
                    return redirect()->route('user.deposit.success');
                case Payment_Sent:
                    return redirect()->route('merchant.payment.success');
                default:
            }

        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect()->to('payment/fail');
        }
    }

    public function cancelPayment(Request $request, PaymentProcessor $processor)
    {
        try {
            $processor->callback($request);
            $this->helper->one_time_message('error', __('You have cancelled your payment'));
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
        }

        return redirect()->to('payment/fail');

    }

}
