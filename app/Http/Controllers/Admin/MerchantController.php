<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Users\EmailController;
use Common, Config, DB, Exception, Validator;
use App\DataTables\Admin\MerchantsDataTable;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MerchantsExport;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{Currency,
    MerchantPayment,
    MerchantGroup,
    MerchantApp,
    Merchant
};

class MerchantController extends Controller
{
    protected $helper;
    protected $email;
    protected $merchant;

    public function __construct()
    {
        $this->helper   = new Common();
        $this->email    = new EmailController();
        $this->merchant = new Merchant();
    }

    public function index(MerchantsDataTable $dataTable)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'merchant_details';

        $data['merchants_status'] = $this->merchant->select('status')->groupBy('status')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';
        $data['user']     = $user    = isset(request()->user_id) ? request()->user_id : null;
        $data['getName']  = $this->merchant->getMerchantsUserName($user);

        return $dataTable->render('admin.merchants.list', $data);
    }

    public function merchantCsv()
    {
        return Excel::download(new MerchantsExport(), 'merchants_list_' . time() . '.xlsx');
    }

    public function merchantPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;
        $user = isset(request()->user_id) ? request()->user_id : null;

        $data['merchants'] = $this->merchant->getMerchantsList($from, $to, $status, $user)->orderBy('merchants.id', 'desc')->get();

        if (isset($from) && isset($to)) {
            $data['date_range'] = $from . ' To ' . $to;
        } else {
            $data['date_range'] = 'N/A';
        }

        generatePDF('admin.merchants.merchants_report_pdf', 'merchants_report_', $data);
    }

    public function merchantsUserSearch(Request $request)
    {
        $search = $request->search;
        $user   = $this->merchant->getMerchantsUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($user) > 0)
        {
            $res = [
                'status' => 'success',
                'data'   => $user,
            ];
        }
        return json_encode($res);
    }

    public function edit($id)
    {
        $data['menu']     = 'users';
        $data['sub_menu'] = 'merchant_details';
        $data['merchant'] = Merchant::find($id);
        $data['merchantGroup'] = MerchantGroup::get(['id','name']);
        $data['activeCurrencies'] = Currency::where(['status' => 'Active', 'type' => 'fiat'])->get(['id', 'code', 'type']);

        return view('admin.merchants.edit', $data);
    }

    public function update(Request $request)
    {
        $rules = array(
            'business_name' => 'required',
            'site_url' => 'required|url',
            'fee' => 'required|numeric',
            'logo' => ['nullable', new CheckValidFile(getFileExtensions(3))],
        );

        $fieldNames = array(
            'business_name' => 'Business Name',
            'site_url'      => 'Site url',
            'fee'           => 'Fee',
            'logo'          => 'Logo',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {

            $fileName = null;

            try {
                DB::beginTransaction();
                $merchant                    = Merchant::find($request->id);
                $merchant->currency_id       = $request->currency_id;
                $merchant->merchant_group_id = $request->merchantGroup;
                $merchant->type              = $request->type;

                if ($request->has('logo')) {
                    $response = uploadImage($request->logo, getDirectory('merchant'), '100*80', $merchant->logo, '70*70');
                    if (true === $response['status']) {
                        $fileName = $response['file_name'];
                    }
                }
                
                if ($request->type == 'express') {
                    $checkMerchantApp = MerchantApp::where(['merchant_id' => $request->id])->first();

                    if (empty($checkMerchantApp)) {
                        $merchant->appInfo()->create([
                            'client_id'     => Str::random(30),
                            'client_secret' => Str::random(100),
                        ]);
                    } else {
                        $merchantApp                = MerchantApp::find($checkMerchantApp->id);
                        $merchantApp->client_id     = $checkMerchantApp->client_id;
                        $merchantApp->client_secret = $checkMerchantApp->client_secret;
                        $merchant->save();
                    }
                }
                $merchant->business_name = $request->business_name;
                $merchant->site_url      = $request->site_url;
                $merchant->fee           = $request->fee;
                if ($fileName != null) {
                    $merchant->logo = $fileName;
                }
                $merchant->status = $request->status;
                $merchant->save();

                DB::commit();
                $this->helper->one_time_message('success', __('The :x has been successfully updated.', ['x' => __('merchant')]));
                return redirect(config('adminPrefix').'/merchants');
            } catch (Exception $e) {
                DB::rollBack();
                $this->helper->one_time_message('error', $e->getMessage());
                return redirect(config('adminPrefix').'/merchants');
            }
        }
    }

    public function deleteMerchantLogo(Request $request)
    {
        $logo = $request->logo;
        if (isset($logo))
        {
            $merchant = Merchant::where(['id' => $request->merchant_id, 'logo' => $request->logo])->first();

            if ($merchant)
            {
                Merchant::where(['id' => $request->merchant_id, 'logo' => $request->logo])->update(['logo' => null]);

                if ($logo != null)
                {
                    $dir = public_path('user_dashboard/merchant/' . $logo);
                    if (file_exists($dir))
                    {
                        unlink($dir);
                    }
                }
                $data['success'] = 1;
                $data['message'] = __('The :x has been successfully deleted.', ['x' => __('logo')]);
            }
            else
            {
                $data['success'] = 0;
                $data['message'] = __('The :x does not exist.', ['x' => __('logo')]);
            }
        }
        echo json_encode($data);
        exit();
    }

    public function eachMerchantPayment($id)
    {
        $data['menu'] = 'users';
        $data['sub_menu'] = 'merchant_details';
        $data['merchant_payments'] = MerchantPayment::where(['merchant_id' => $id])->orderBy('id', 'desc')->get();
        $data['merchant'] = Merchant::find($id);
        return view('admin.merchants.eachMerchantPayment', $data);
    }

    public function changeMerchantFeeWithGroupChange(Request $request)
    {
        if ($request->merchant_group_id)
        {
            $merchantGroup = MerchantGroup::where(['id' => $request->merchant_group_id])->first(['fee']);
            if ($merchantGroup)
            {
                $data['status'] = true;
                $data['fee']    = $merchantGroup->fee;
            }
            else
            {
                $data['status'] = false;
            }
            return $data;
        }
    }
}
