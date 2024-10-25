<?php

namespace App\Http\Controllers\Users;

use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use Illuminate\Support\Str;
use DB, Auth, Exception;
use App\Models\{Wallet,
    MerchantPayment,
    MerchantGroup,
    Currency,
    Merchant,
    QrCode
};

class MerchantController extends Controller
{
    protected $helper, $merchantPayment;

    public function __construct()
    {
        $this->helper = new Common();
        $this->merchantPayment = new MerchantPayment();
    }

    public function index()
    {
        $data['menu']          = 'merchant';
        $data['sub_menu']      = 'merchant';
        $data['content_title'] = 'Merchant';
        $data['icon']          = 'user';
        $data['merchants']          = Merchant::with(['appInfo', 'currency:id,code,type'])->where(['user_id' => Auth::user()->id])->orderBy('id', 'desc')->paginate(10);

        $data['defaultWallet'] = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']);

        return view('user.merchant.index', $data);
    }

    public function add()
    {
        $data['menu']     = 'merchant';
        $data['sub_menu'] = 'merchant';

        $data['activeCurrencies'] = Currency::where(['status' => 'Active', 'type' => 'fiat'])->get(['id', 'code']);
        $data['defaultWallet']    = Wallet::where(['user_id' => auth()->user()->id, 'is_default' => 'Yes'])->first(['currency_id']);

        return view('user.merchant.create', $data);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'business_name' => 'required|unique:merchants,business_name',
            'site_url'      => 'required|url',
            'type'          => 'required',
            'note'          => 'required',
            'logo' => ['nullable', new CheckValidFile(getFileExtensions(3), true)],
        ]);

        try {
            DB::beginTransaction();

            $picture  = $request->logo;
            $fileName = null;

            if (isset($picture)) {
                $response = uploadImage($picture, public_path("/uploads/merchant/"),'100*80', null, '70*70');

                if ($response['status'] === true) {
                    $fileName = $response['file_name'];
                } else {
                    DB::rollBack();
                    $this->helper->one_time_message('error', $response['message']);
                    return back()->withInput();
                }
            }

            $merchantGroup               = MerchantGroup::where(['is_default' => 'Yes'])->select('id', 'fee')->first();
            $merchant                    = new Merchant();
            $merchant->user_id           = Auth::user()->id;
            $merchant->currency_id       = $request->currency_id;
            $merchant->merchant_group_id = isset($merchantGroup) ? $merchantGroup->id : null;
            $merchant->business_name     = $request->business_name;
            $merchant->site_url          = $request->site_url;
            $uuid                        = unique_code();
            $merchant->merchant_uuid     = $uuid;
            $merchant->type              = $request->type;
            $merchant->note              = $request->note;
            $merchant->logo              = $fileName != null ? $fileName : '';
            $merchant->fee               = isset($merchantGroup) ? $merchantGroup->fee : 0.00;

            if (module('WithdrawalApi') && isActive('WithdrawalApi')) {
                $merchant->withdrawal_approval = $request->withdrawal_approval == 'on' ? 'Yes' : 'No';
            }

            $merchant->save();

            if (strtolower($request->type) == 'express') {
                try {
                    $merchantAppInfo = $merchant->appInfo()->create([
                        'client_id'     => Str::random(30),
                        'client_secret' => Str::random(100),
                    ]);
                } catch (Exception $ex) {
                    DB::rollBack();
                    $this->helper->one_time_message('error', __('Client id must be unique. Please try again!'));
                    return back();
                }

                $request->request->add([
                    'merchantId' => $merchant->id, 
                    'merchantDefaultCurrencyId' => $merchant->currency_id,
                    'clientId' => $merchantAppInfo->client_id
                ]);

                $this->generateOrUpdateExpressMerchantQrCode($request);
            }

            DB::commit();

            // Notification Email/SMS
            (new  \App\Services\Mail\MerchantPayment\NotifyAdminOnMerchantCreationMailService())->send($merchant, ['type' => 'payment', 'medium' => 'email']);
            $this->helper->one_time_message('success', __('Merchant Created Successfully!'));
            return redirect('merchants');
        }
        catch (Exception $e)
        {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('merchants');
        }
    }

    public function edit($id)
    {
        $data['menu']             = 'merchant';
        $data['sub_menu']         = 'merchant';
        $data['content_title']    = 'Merchant';
        $data['icon']             = 'user';
        $data['activeCurrencies'] = Currency::where(['status' => 'Active', 'type' => 'fiat'])->get(['id', 'code']);
        $data['merchant']         = $merchant = Merchant::with('currency:id,code')->find($id);
        $data['defaultWallet']    = Wallet::with(['currency:id,code'])->where(['user_id' => $merchant->user->id, 'is_default' => 'Yes'])->first(['currency_id']);

        if (!isset($merchant) || $merchant->user_id != Auth::user()->id)
        {
            abort(404);
        }
        return view('user.merchant.edit', $data);
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'business_name' => 'required|unique:merchants,business_name,' . $request->id,
            'site_url'      => 'required|url',
            'note'          => 'required',
            'logo'          => ['nullable', new CheckValidFile(getFileExtensions(3), true)],
        ]);

        $picture  = $request->logo;
        $fileName = null;

        try {
            DB::beginTransaction();

            $merchant = Merchant::find($request->id, ['id', 'currency_id', 'business_name', 'site_url', 'note', 'logo']);
            
            if (isset($picture)) {
                $response = uploadImage($picture, public_path("/uploads/merchant/"),'100*80', $merchant->logo, '70*70');
                if ($response['status'] === true) {
                    $fileName = $response['file_name'];
                } else {
                    DB::rollBack();
                    $this->helper->one_time_message('error', $response['message']);
                    return back()->withInput();
                }
            }

            $merchant->business_name = $request->business_name;
            $merchant->site_url      = $request->site_url;
            $merchant->note          = $request->note;
            if ($fileName != null) {
                $merchant->logo = $fileName;
            }
            
            if ($merchant->currency_id != $request->currency_id) {
                $merchant->status = 'Moderation';
            }
            $merchant->currency_id   = $request->currency_id;

            if (module('WithdrawalApi') && isActive('WithdrawalApi')) {
                $merchant->withdrawal_approval = $request->withdrawal_approval == 'on' ? 'Yes' : 'No';
            }

            $merchant->save();

            DB::commit();
            $this->helper->one_time_message('success', __('Merchant Updated Successfully!'));
            return redirect('merchants');
        } catch (Exception $e) {
            DB::rollBack();
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('merchants');
        }
        
    }

    public function detail($id)
    {
        $data['menu']          = 'merchant';
        $data['sub_menu']      = 'merchant';
        $data['content_title'] = 'Merchant';
        $data['icon']          = 'user';
        $data['merchant']      = $merchant      = Merchant::find($id);
        $data['defaultWallet'] = Wallet::with(['currency:id,code'])->where(['user_id' => $merchant->user->id, 'is_default' => 'Yes'])->first(['currency_id']); //new
        if (!isset($merchant) || $merchant->user_id != Auth::user()->id)
        {
            abort(404);
        }
        return view('user_dashboard.Merchant.detail', $data);
    }

    public function payments()
    {
        $merchants = Merchant::where('user_id', auth()->id())->get(['id', 'business_name']);
        $from = isset(request()->from) ? setDateForDb(request()->from) : null;
        $to = isset(request()->to) ? setDateForDb(request()->to) : null;
        $status = isset(request()->status) ? request()->status : 'all';
        $currency = isset(request()->currency) ? request()->currency : 'all';
        $paymentMethod = isset(request()->paymentMethod) ? request()->paymentMethod : 'all';
        $orderNumber = isset(request()->orderNumber) ? request()->orderNumber : null;
        $merchant = isset(request()->merchant) && request()->merchant != 'all' ? request()->merchant : $merchants->pluck('id')->toArray();
        
        $data = [
            'to' => $to,
            'from' => $from,
            'status' => $status,
            'currency' => $currency,
            'paymentMethod' => $paymentMethod,
            'orderNumber' => $orderNumber,
            'merchant' => $merchant,
            'merchants' => $merchants,
            'icon' => 'user',
            'menu' => 'merchant_payment',
            'sub_menu' => 'merchant_payment',
            'content_title' => 'Merchant payments',
            'paymentMethods' => $this->merchantPayment->with('payment_method:id,name')->whereNotNull('payment_method_id')->groupBy('payment_method_id')->get(['payment_method_id']),
            'statuses' => $this->merchantPayment->groupBy('status')->get(['status']),
            'currencies' => $this->merchantPayment->with('currency:id,code')->groupBy('currency_id')->get(['currency_id']),
            'merchantPayments' => $this->merchantPayment->getMerchantPaymentsList($from, $to, $status, $currency, $paymentMethod, $orderNumber, $merchant)->latest()->paginate(15)
            
        ];

        return view('user.merchant.payments', $data);
    }

    //Standard Merchant QrCode - starts
    public function generateStandardMerchantPaymentQrCode(Request $request) 
    {
        $qrCode = QrCode::where(['object_id' => $request->merchantId, 'object_type' => 'standard_merchant', 'status' => 'Active'])->first(['id', 'secret']);
        $merchantCurrency = Currency::where('id', $request->merchantDefaultCurrency)->first(['code']);
        
        if (!empty($qrCode)) {
            $qrCode->status = 'Inactive';
            $qrCode->save();
        }


        $secretCode = convert_string('encrypt', 'standard_merchant' . '-' . $request->merchantId . '-' . $merchantCurrency->code . '-' . $request->paymentAmount . '-' . Str::random(6));
        $imageName = time() . '.' . 'jpg';

        $createMerchantQrCode = new QrCode();
        $createMerchantQrCode->object_id   = $request->merchantId;
        $createMerchantQrCode->object_type = 'standard_merchant';
        $createMerchantQrCode->secret = $secretCode;
        $createMerchantQrCode->qr_image = $imageName;
        $createMerchantQrCode->status = 'Active';
        $createMerchantQrCode->save();
        

        $secretCodeImage = generateQrcode($createMerchantQrCode->secret);
        Image::make($secretCodeImage)->save(getDirectory('merchant_qrcode') . $imageName); 

        return response()->json([
            'status' => true,
            'imgSource' => image($imageName, 'merchant_qrcode')
        ]);
    }

    public function getExpressMerchantQrCode(Request $request)
    {
        $qrCode = $qrCode = QrCode::where(['object_id' => $request->merchantId, 'object_type' => 'express_merchant', 'status' => 'Active'])->first(['qr_image']);

        return response()->json([
            'status' => true,
            'imgSource' => image($qrCode?->qr_image, 'merchant_qrcode')
        ]);
    }

    public function generateOrUpdateExpressMerchantQrCode(Request $request) 
    {
        $qrCode = QrCode::where(['object_id' => $request->merchantId, 'object_type' => 'express_merchant', 'status' => 'Active'])->first(['id', 'secret']);
        $merchantCurrency = Currency::where('id', $request->merchantDefaultCurrencyId)->first(['code']);

        if (!empty($qrCode)) {
            $qrCode->status = 'Inactive';
            $qrCode->save();
        }


        $secretCode = convert_string('encrypt', 'express_merchant' . '-' . $request->merchantId . '-' . $merchantCurrency->code . '-' . $request->clientId . Str::random(6));

        $imageName = time() . '.' . 'jpg';

        $createMerchantQrCode = new QrCode();
        $createMerchantQrCode->object_id   = $request->merchantId;
        $createMerchantQrCode->object_type = 'express_merchant';
        $createMerchantQrCode->secret = $secretCode;
        $createMerchantQrCode->qr_image = $imageName;
        $createMerchantQrCode->status = 'Active';
        $createMerchantQrCode->save();
        
        $secretCodeImage = generateQrcode($createMerchantQrCode->secret);
        Image::make($secretCodeImage)->save(getDirectory('merchant_qrcode') . $imageName); 

        return response()->json([
            'status' => true,
            'imgSource' => image($imageName, 'merchant_qrcode')
        ]);
        
    }

    public function printMerchantQrCode($merchantId, $objectType) {
        $this->helper->printQrCode($merchantId, $objectType);
    }
}
