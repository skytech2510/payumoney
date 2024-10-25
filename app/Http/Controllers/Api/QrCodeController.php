<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{Currency,
    Merchant,
    MerchantPayment,
    QrCode,
    Transaction,
    User,
    Wallet
};
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QrCodeController extends Controller
{
    public $successStatus      = 200;
    public $unauthorisedStatus = 401;

    public function getUserQrCode()
    {
        $qrCode = QrCode::where(['object_id' => request('user_id'), 'object_type' => 'user', 'status' => 'Active'])->first(['secret']);
        if (!empty($qrCode))
        {
            return response()->json([
                'status' => $this->successStatus,
                'secret' => urlencode($qrCode->secret),
            ]);
        }
        else
        {
            return response()->json([
                'status' => $this->unauthorisedStatus,
            ]);
        }
    }

    public function addOrUpdateUserQrCode()
    {
        $user_id = request('user_id');
        $user = User::where(['id' => $user_id, 'status' => 'Active'])->first(['id', 'formattedPhone', 'email']);
        $qrCode = QrCode::updateQrCode($user);
        return response()->json([
            'status' => $this->successStatus,
            'secret' => urlencode($qrCode->secret)
        ]);
    }

    public function performQrCodeOperationApi()
    {
        $qrCode = QrCode::where(['secret' => request('resultText'), 'status' => 'Active'])->whereIn('object_type', ['standard_merchant', 'express_merchant'])->first(['status']);
        if (isset($qrCode) && $qrCode->status == 'Active')
        {
            $result   = convert_string('decrypt', request('resultText'));
            $data     = explode('-', $result);
            $userType = $data[0];
            if ($userType == 'standard_merchant')
            {
                $merchantId                  = $data[1];
                $merchantDefaultCurrencyCode = $data[2];
                $merchantPaymentAmount       = $data[3];
                // $merchantPaymentAmount       = number_format((float) $merchantPaymentAmount, 2, '.', '');
                $merchantPaymentAmount = $merchantPaymentAmount;

                return response()->json([
                    'status'                      => true,
                    'userType'                    => $userType,
                    'merchantId'                  => $merchantId,
                    'merchantDefaultCurrencyCode' => $merchantDefaultCurrencyCode,
                    'merchantPaymentAmount'       => $merchantPaymentAmount,
                ]);
            }
            elseif ($userType == 'express_merchant')
            {
                $merchantId                  = $data[1];
                $merchantDefaultCurrencyCode = $data[2];

                return response()->json([
                    'status'                      => true,
                    'userType'                    => $userType,
                    'merchantId'                  => $merchantId,
                    'merchantDefaultCurrencyCode' => $merchantDefaultCurrencyCode,
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Invalid QR Code!',
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 404,
                'message' => 'Invalid QR Code!',
            ]);
        }
    }

    public function performSendMoneyRequestMoneyQrCodeOperationApi()
    {

        $qrCode = QrCode::where(['secret' => request('resultText'), 'object_type' => 'user', 'status' => 'Active'])->first(['status']);
        if (isset($qrCode) && $qrCode->status == 'Active')
        {
            $result   = convert_string('decrypt', request('resultText'));
            $data     = explode('-', $result);
            $userType = $data[0];

            if ($userType == 'user')
            {
                $receiverEmail = $data[1]; //Email is taken as email is mandatory in registration; not phone
                return response()->json([
                    'status'        => true,
                    'userType'      => $userType,
                    'receiverEmail' => $receiverEmail,
                ]);
            }
            else
            {
                return response()->json([
                    'status'  => 404,
                    'message' => 'Invalid User!',
                ]);
            }
        }
        else
        {
            return response()->json([
                'status'  => 404,
                'message' => 'Invalid QR Code!',
            ]);
        }
    }

    //Standard Merchant QR Code Payment - starts
    public function performMerchantPaymentQrCodeReviewApi()
    {
        //Check merchant
        $merchant = Merchant::find(request('merchantId'), ['id', 'user_id', 'fee', 'business_name']);
        if (!$merchant)
        {
            return response()->json(
                [
                    'status'  => 401,
                    'message' => 'Merchant does not exist!',
                ]
            );
        }

        //merchant cannot make payment to himself
        if ($merchant->user_id == request('user_id'))
        {
            return response()->json(
                [
                    'status'  => 401,
                    'message' => 'Merchant cannot make payment to himself!',
                ]
            );
        }

        //Check currency
        $curr = Currency::where('code', request('merchantDefaultCurrencyCode'))->first(['id', 'symbol']);
        if (!$curr)
        {
            return response()->json(
                [
                    'status'  => 401,
                    'message' => 'Currency - ' . request('merchantDefaultCurrencyCode') . ' - not found!',
                ]
            );
        }

        //Check user's wallets against merchant wallet
        $acceptedCurrency = [];
        $wallets          = Wallet::with(['user:id', 'currency:id,code'])->where(['user_id' => request('user_id')])->get(['currency_id']);
        foreach ($wallets as $wallet)
        {
            $acceptedCurrency[] = $wallet->currency->code;
        }
        if (!in_array(request('merchantDefaultCurrencyCode'), $acceptedCurrency))
        {
            return response()->json(
                [
                    'status'  => 401,
                    'message' => 'You do not have ' . request('merchantDefaultCurrencyCode') . ' wallet. Please exchange to ' . request('merchantDefaultCurrencyCode') . ' wallet!',
                ]
            );
        }

        //Check Balance
        $merchantPaymentAmount = request('merchantPaymentAmount');
        $senderWallet          = Wallet::where(['user_id' => request('user_id'), 'currency_id' => $curr->id])->first(['balance']);
        if ($senderWallet->balance < $merchantPaymentAmount)
        {
            return response()->json([
                'status'  => 401,
                'message' => 'Sorry, not enough funds to perform the operation.',
            ]);
        }

        //Data for success below
        $merchantCalculatedChargePercentageFee = ($merchant->fee * $merchantPaymentAmount) / 100;

        return response()->json([
            'status'                                => 200,
            'merchantBusinessName'                  => $merchant->business_name,
            'merchantPaymentCurrencySymbol'         => $curr->symbol,
            'merchantPaymentAmount'                 => $merchantPaymentAmount,
            'merchantCalculatedChargePercentageFee' => $merchantCalculatedChargePercentageFee,
            //below needed for merchant payment submit
            'merchantActualFee'                     => $merchant->fee,
            'merchantCurrencyId'                    => $curr->id,
            'merchantUserId'                        => $merchant->user_id,
        ]);
    }

    public function performMerchantPaymentQrCodeSubmit()
    {
        $unique_code           = unique_code();
        $merchantPaymentAmount = request('merchantPaymentAmount');
        $merchantActualFee     = request('merchantActualFee');
        $merchantCurrencyId    = request('merchantCurrencyId');
        $merchantUserId        = request('merchantUserId');
        $merchantId            = request('merchantId');
        $user_id               = request('user_id');

        $p_calc = ($merchantActualFee * $merchantPaymentAmount) / 100;

        try
        {
            DB::beginTransaction();

            //Merchant Payment
            $merchantPayment                    = new MerchantPayment();
            $merchantPayment->merchant_id       = $merchantId;
            $merchantPayment->currency_id       = $merchantCurrencyId;
            $merchantPayment->payment_method_id = 1;
            $merchantPayment->user_id           = $user_id;
            $merchantPayment->gateway_reference = $unique_code;
            $merchantPayment->order_no          = '';
            $merchantPayment->item_name         = '';
            $merchantPayment->uuid              = $unique_code;
            $merchantPayment->charge_percentage = $p_calc;
            $merchantPayment->charge_fixed      = 0;
            $merchantPayment->amount            = $merchantPaymentAmount - $p_calc;
            $merchantPayment->total             = $merchantPaymentAmount;
            $merchantPayment->status            = 'Success';
            $merchantPayment->save();

            //Payment_Sent
            $transaction_A                           = new Transaction();
            $transaction_A->user_id                  = $user_id;
            $transaction_A->end_user_id              = $merchantUserId;
            $transaction_A->currency_id              = $merchantCurrencyId;
            $transaction_A->payment_method_id        = 1;
            $transaction_A->merchant_id              = $merchantId;
            $transaction_A->uuid                     = $unique_code;
            $transaction_A->transaction_reference_id = $merchantPayment->id;
            $transaction_A->transaction_type_id      = Payment_Sent;
            $transaction_A->subtotal                 = $merchantPaymentAmount;
            $transaction_A->percentage               = $merchantActualFee;
            $transaction_A->charge_percentage        = 0;
            $transaction_A->charge_fixed             = 0;
            $transaction_A->total                    = '-' . ($merchantPayment->charge_percentage + $merchantPayment->amount);
            $transaction_A->status                   = 'Success';
            $transaction_A->save();

            //Payment_Received
            $transaction_B                           = new Transaction();
            $transaction_B->user_id                  = $merchantUserId;
            $transaction_B->end_user_id              = $user_id;
            $transaction_B->currency_id              = $merchantCurrencyId;
            $transaction_B->payment_method_id        = 1;
            $transaction_B->merchant_id              = $merchantId;
            $transaction_B->uuid                     = $unique_code;
            $transaction_B->transaction_reference_id = $merchantPayment->id;
            $transaction_B->transaction_type_id      = Payment_Received;
            $transaction_B->subtotal                 = $merchantPaymentAmount - ($p_calc);
            $transaction_B->percentage               = $merchantActualFee; //fixed
            $transaction_B->charge_percentage        = $p_calc;
            $transaction_B->charge_fixed             = 0;
            $transaction_B->total                    = $merchantPayment->charge_percentage + $merchantPayment->amount;
            $transaction_B->status                   = 'Success';
            $transaction_B->save();

            //updating sender/user wallet
            $senderWallet          = Wallet::where(['user_id' => $user_id, 'currency_id' => $merchantCurrencyId])->first(['id', 'balance', 'user_id']);
            $senderWallet->balance = $senderWallet->balance - $merchantPaymentAmount;
            $senderWallet->save();

            //updating merchant wallet
            $merchantWallet          = Wallet::where(['user_id' => $merchantUserId, 'currency_id' => $merchantCurrencyId])->first(['id', 'balance']);
            $merchantWallet->balance = $merchantWallet->balance + ($merchantPaymentAmount - $p_calc); //fixed -- not amount with fee(total); only amount)
            $merchantWallet->save();

            DB::commit();

            return response()->json([
                'status' => $this->successStatus,
            ]);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status'  => $this->unauthorisedStatus,
                'message' => $e->getMessage(),
            ]);
        }
    }
    //Standard Merchant QR Code Payment - ends

    //Express Merchant QR Code Payment - starts
    public function performExpressMerchantPaymentMerchantCurrencyUserWalletsReviewApi()
    {
        //Check merchant
        $merchant = Merchant::find(request('expressMerchantId'), ['id', 'user_id', 'fee', 'business_name']);
        if (!$merchant)
        {
            return response()->json(
                [
                    'status'  => 401,
                    'message' => 'Merchant does not exist!',
                ]
            );
        }

        //merchant cannot make payment to himself
        if ($merchant->user_id == request('user_id'))
        {
            return response()->json(
                [
                    'status'  => 401,
                    'message' => 'Merchant cannot make payment to himself!',
                ]
            );
        }

        //Check currency
        $curr = Currency::where('code', request('expressMerchantPaymentCurrencyCode'))->first(['id', 'symbol']);
        if (!$curr)
        {
            return response()->json(
                [
                    'status'  => 401,
                    'message' => 'Currency - ' . request('expressMerchantPaymentCurrencyCode') . ' - not found!',
                ]
            );
        }

        //Check user's wallets against merchant wallet
        $acceptedCurrency = [];
        $wallets          = Wallet::with(['user:id', 'currency:id,code'])->where(['user_id' => request('user_id')])->get(['currency_id']);
        foreach ($wallets as $wallet)
        {
            $acceptedCurrency[] = $wallet->currency->code;
        }
        if (!in_array(request('expressMerchantPaymentCurrencyCode'), $acceptedCurrency))
        {
            return response()->json(
                [
                    'status'  => 401,
                    'message' => 'You do not have ' . request('expressMerchantPaymentCurrencyCode') . ' wallet. Please exchange to ' . request('expressMerchantPaymentCurrencyCode') . ' wallet!',
                ]
            );
        }

        return response()->json([
            'status'                               => 200,
            //below needed for merchant payment submit
            'expressMerchantBusinessName'          => $merchant->business_name,
            'expressMerchantPaymentCurrencyId'     => $curr->id,
            'expressMerchantPaymentCurrencySymbol' => $curr->symbol,
            'expressMerchantActualFee'             => $merchant->fee,
            'expressMerchantUserId'                => $merchant->user_id,
        ]);
    }

    public function performExpressMerchantPaymentAmountReviewApi()
    {
        //Check Balance
        $expressMerchantPaymentAmount = request('expressMerchantPaymentAmount');
        $senderWallet                 = Wallet::where(['user_id' => request('user_id'), 'currency_id' => request('expressMerchantPaymentCurrencyId')])->first(['balance']);
        if ($senderWallet->balance < $expressMerchantPaymentAmount)
        {
            return response()->json([
                'status'  => 401,
                'message' => 'Sorry, not enough funds to perform the operation.',
            ]);
        }
        //Data for success below
        $expressMerchantCalculatedChargePercentageFee = (request('expressMerchantActualFee') * $expressMerchantPaymentAmount) / 100;

        return response()->json([
            'status'                                       => 200,
            'expressMerchantCalculatedChargePercentageFee' => $expressMerchantCalculatedChargePercentageFee,
        ]);
    }

    public function performExpressMerchantPaymentQrCodeSubmit()
    {
        $unique_code                      = unique_code();
        $expressMerchantPaymentAmount     = request('expressMerchantPaymentAmount');
        $expressMerchantActualFee         = request('expressMerchantActualFee');
        $expressMerchantPaymentCurrencyId = request('expressMerchantPaymentCurrencyId');
        $expressMerchantUserId            = request('expressMerchantUserId');
        $expressMerchantId                = request('expressMerchantId');
        $user_id                          = request('user_id');

        $p_calc = ($expressMerchantActualFee * $expressMerchantPaymentAmount) / 100;

        try
        {
            DB::beginTransaction();

            //Merchant Payment
            $merchantPayment                    = new MerchantPayment();
            $merchantPayment->merchant_id       = $expressMerchantId;
            $merchantPayment->currency_id       = $expressMerchantPaymentCurrencyId;
            $merchantPayment->payment_method_id = 1;
            $merchantPayment->user_id           = $user_id;
            $merchantPayment->gateway_reference = $unique_code;
            $merchantPayment->order_no          = '';
            $merchantPayment->item_name         = '';
            $merchantPayment->uuid              = $unique_code;
            $merchantPayment->charge_percentage = $p_calc;
            $merchantPayment->charge_fixed      = 0;
            $merchantPayment->amount            = $expressMerchantPaymentAmount - $p_calc;
            $merchantPayment->total             = $expressMerchantPaymentAmount;
            $merchantPayment->status            = 'Success';
            $merchantPayment->save();

            //Payment_Sent
            $transaction_A                           = new Transaction();
            $transaction_A->user_id                  = $user_id;
            $transaction_A->end_user_id              = $expressMerchantUserId;
            $transaction_A->currency_id              = $expressMerchantPaymentCurrencyId;
            $transaction_A->payment_method_id        = 1;
            $transaction_A->merchant_id              = $expressMerchantId;
            $transaction_A->uuid                     = $unique_code;
            $transaction_A->transaction_reference_id = $merchantPayment->id;
            $transaction_A->transaction_type_id      = Payment_Sent;
            $transaction_A->subtotal                 = $expressMerchantPaymentAmount;
            $transaction_A->percentage               = $expressMerchantActualFee;
            $transaction_A->charge_percentage        = 0;
            $transaction_A->charge_fixed             = 0;
            $transaction_A->total                    = '-' . ($merchantPayment->charge_percentage + $merchantPayment->amount);
            $transaction_A->status                   = 'Success';
            $transaction_A->save();

            //Payment_Received
            $transaction_B                           = new Transaction();
            $transaction_B->user_id                  = $expressMerchantUserId;
            $transaction_B->end_user_id              = $user_id;
            $transaction_B->currency_id              = $expressMerchantPaymentCurrencyId;
            $transaction_B->payment_method_id        = 1;
            $transaction_B->merchant_id              = $expressMerchantId;
            $transaction_B->uuid                     = $unique_code;
            $transaction_B->transaction_reference_id = $merchantPayment->id;
            $transaction_B->transaction_type_id      = Payment_Received;
            $transaction_B->subtotal                 = $expressMerchantPaymentAmount - ($p_calc);
            $transaction_B->percentage               = $expressMerchantActualFee; //fixed
            $transaction_B->charge_percentage        = $p_calc;
            $transaction_B->charge_fixed             = 0;
            $transaction_B->total                    = $merchantPayment->charge_percentage + $merchantPayment->amount;
            $transaction_B->status                   = 'Success';
            $transaction_B->save();

            //updating sender/user wallet
            $senderWallet          = Wallet::where(['user_id' => $user_id, 'currency_id' => $expressMerchantPaymentCurrencyId])->first(['id', 'balance', 'user_id']);
            $senderWallet->balance = $senderWallet->balance - $expressMerchantPaymentAmount;
            $senderWallet->save();

            //updating/Creating merchant wallet
            $merchantWallet = Wallet::where(['user_id' => $expressMerchantUserId, 'currency_id' => $expressMerchantPaymentCurrencyId])->first(['id', 'balance']);
            if (empty($merchantWallet))
            {
                $wallet              = new Wallet();
                $wallet->user_id     = $expressMerchantUserId;
                $wallet->currency_id = $expressMerchantPaymentCurrencyId;
                $wallet->balance     = ($expressMerchantPaymentAmount - $p_calc);
                $wallet->is_default  = 'No';
                $wallet->save();
            }
            else
            {
                $merchantWallet->balance = $merchantWallet->balance + ($expressMerchantPaymentAmount - $p_calc); //fixed -- not amount with fee(total); only amount)
                $merchantWallet->save();
            }

            DB::commit();

            return response()->json([
                'status' => $this->successStatus,
            ]);
        }
        catch (Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'status'  => $this->unauthorisedStatus,
                'message' => $e->getMessage(),
            ]);
        }
    }
    //Express Merchant QR Code Payment - ends
}
