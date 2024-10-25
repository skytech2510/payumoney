<?php

namespace App\Http\Controllers;

use App\Exceptions\ExpressMerchantPaymentException;
use App\Services\ExpressMerchantPaymentService;
use Session, Auth, Exception;
use App\Http\Helpers\Common;
use Illuminate\Http\Request;
use App\Models\{
    AppTransactionsInfo,
    Currency
};

class ExpressMerchantPaymentController extends Controller
{
    protected $helper, $service;
    public function __construct()
    {
        $this->helper = new Common();
        $this->service = new ExpressMerchantPaymentService();
    }

    public function verifyClient(Request $request)
    {
        try {
            $app = $this->service->verifyClientCredentials($request->client_id, $request->client_secret);
            $response = $this->service->createAccessToken($app);
            return json_encode($response);

        } catch (ExpressMerchantPaymentException $exception) {
            $data = [
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ];
            return json_encode($data);

        } catch(Exception $exception) {
            $data = [
                'status'  => 'error',
                'message' => __("Failed to process the request."),
            ];
            return json_encode($data);
        }
        
    }

    public function storeTransactionInfo(Request $request)
    {
        try {
            $paymentMethod = $request->payer;
            $amount        = $request->amount;
            $currency      = $request->currency;
            $successUrl    = $request->successUrl;
            $cancelUrl     = $request->cancelUrl;

            # check token missing
            $hasHeaderAuthorization = $request->hasHeader('Authorization');
            if (!$hasHeaderAuthorization) {
                $res = [
                    'status'  => 'error',
                    'message' => __('Access token is missing'),
                    'data'    => [],
                ];
                return json_encode($res);
            }

            # check token authorization
            $headerAuthorization = $request->header('Authorization');
            $token = $this->service->checkTokenAuthorization($headerAuthorization);

            # Currency And Amount Validation
            $res = $this->service->checkMerchantWalletAvailability($token, $currency, $amount);

            # Update/Create AppTransactionsInfo and return response
            $res = $this->service->createAppTransactionsInfo($token->app_id, $paymentMethod, $amount, $currency, $successUrl, $cancelUrl);
            return json_encode($res);
        } catch (ExpressMerchantPaymentException $exception) {
            $data = [
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ];
            return json_encode($data);

        } catch(Exception $exception) {
            $data = [
                'status'  => 'error',
                'message' => __("Failed to process the request."),
            ];
            return json_encode($data);
        }
    }

    /**
     * [Generat URL]
     * @param  Request $request  [email, password]
     * @return [view]  [redirect to merchant confirm page or redirect back]
     */
    public function generatedUrl(Request $request)
    {
        try {

            $transInfo = $this->service->getTransactionData($request->grant_id, $request->token);
            $currency = Currency::whereCode($transInfo->currency)->first();
            $feesLimit = $this->service->checkMerchantPaymentFeesLimit($currency->id, Mts, $transInfo->amount, $transInfo->app->merchant->fee);

            $totalAmount = $transInfo?->app?->merchant?->merchant_group?->fee_bearer == 'Merchant' ? $transInfo['amount'] : $transInfo['amount'] + $feesLimit['totalFee'];

            if (!auth()->check()) {

                if ($request->isMethod('POST')) {
                    $credentials = $request->only('email', 'password');

                    if (Auth::attempt($credentials)) {

                        $authCredentials = [
                            'email'    => $request->email,
                            'password' => $request->password,
                        ];

                        Session::put('credentials', $authCredentials);
                        //Abort if logged in user is same as merchant
                        $this->checkMerchantUser($transInfo);

                        $this->checkUserStatus(auth()->user()->status);

                        $this->service->checkUserBalance(auth()->user()->id, $totalAmount, $currency->id);

                        $data = $this->service->checkoutToPaymentConfirmPage($transInfo);
                        $data['fees'] = $feesLimit['totalFee'];
                        $data['transInfo'] = $transInfo;
                        $data['currencyId'] = $currency->id;
                        return view('merchantPayment.confirm', $data);
                        
                    } else {
                        $this->helper->one_time_message('error', __('Unable to login with provided credentials!'));
                        return redirect()->back();
                    }
                } else {
                    $data['fees'] = $feesLimit['totalFee'];
                    $data['transInfo'] = $transInfo;
                    $data['currencyId'] = $currency->id;
                    return view('merchantPayment.login', $data);
                }
            }

            //Abort if logged in user is same as merchant
            $this->checkMerchantUser($transInfo);

            $this->checkUserStatus(auth()->user()->status);

            $this->service->checkUserBalance(auth()->user()->id, $totalAmount, $currency->id);

            $data = $this->service->checkoutToPaymentConfirmPage($transInfo);

            $data['fees'] = $feesLimit['totalFee'];
            $data['currencyId'] = $currency->id;

            return view('merchantPayment.confirm', $data);

        } catch (ExpressMerchantPaymentException $exception) {
            $data = [
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ];
            return view('merchantPayment.fail', $data);

        } catch(Exception $exception) {
            $data = [
                'status'  => 'error',
                'message' => __("Failed to process the request."),
            ];
            return view('merchantPayment.fail', $data);
        }
    }

    public function confirmPayment()
    {
        try {

            if (!auth()->check()) {
                $getLoggedInCredentials = Session::get('credentials');
    
                if (Auth::attempt($getLoggedInCredentials)) {
                    $successPath = $this->service->storePaymentInformations();
                    return redirect()->to($successPath);
                } 
    
                $this->helper->one_time_message('error', __('Unable to login with provided credentials!'));
                return redirect()->back();
                
            }
    
            $data = $this->service->storePaymentInformations();
            if ($data['status'] == 200) {
                return redirect()->to($data['successPath']);
            }
            Session::forget('transInfo');

        } catch (ExpressMerchantPaymentException $exception) {
            $data = [
                'status'  => 'error',
                'message' => $exception->getMessage(),
            ];
            return view('merchantPayment.fail', $data);

        } catch(Exception $exception) {
            $data = [
                'status'  => 'error',
                'message' => __("Failed to process the request."),
            ];
            return view('merchantPayment.fail', $data);
        }
        
    }

    public function cancelPayment()
    {
        $transInfo     = Session::get('transInfo');
        $trans         = AppTransactionsInfo::find($transInfo->id, ['id', 'status', 'cancel_url']);
        $trans->status = 'cancel';
        $trans->save();
        Session::forget('transInfo');
        return redirect()->to($trans->cancel_url);
    }

    protected function checkUserStatus($status)
    {
        //Check whether user is Suspended
        if ($status == 'Suspended') {
            $data['message'] = __('You are suspended to do any kind of transaction!');
            return view('merchantPayment.user_suspended', $data);
        }

        //Check whether user is inactive
        if ($status == 'Inactive') {
            auth()->logout();
            $this->helper->one_time_message('danger', __('Your account is inactivated. Please try again later!'));
            return redirect('/login');
        }
    }

    protected function checkMerchantUser(object $transInfo)
    {
        if ($transInfo?->app?->merchant?->user?->id == auth()->user()->id) {
            auth()->logout();
            $this->helper->one_time_message('error', __('Merchant cannot make payment to himself!'));
            return redirect()->back();
        } 
    }
}
