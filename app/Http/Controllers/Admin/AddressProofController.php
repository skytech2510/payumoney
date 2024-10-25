<?php

namespace App\Http\Controllers\Admin;

use Common;
use Illuminate\Http\Request;
use App\Exports\AddressProofsExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\{DocumentVerification, User };
use App\Http\Controllers\Users\EmailController;
use App\DataTables\Admin\AddressProofsDataTable;
use App\Services\Sms\AddressOrIndentityVerificationSmsService;
use App\Services\Mail\AddressOrIndentityVerificationMailService;

class AddressProofController extends Controller
{
    protected $helper;
    protected $addressVerification;
    protected $email;

    public function __construct()
    {
        $this->helper              = new Common();
        $this->addressVerification = new DocumentVerification();
        $this->email               = new EmailController();
    }

    public function index(AddressProofsDataTable $dataTable)
    {
        $data['menu']     = 'proofs';
        $data['sub_menu'] = 'address-proofs';

        $data['documentVerificationStatus'] = $this->addressVerification->where(['verification_type' => 'address'])->select('status')->groupBy('status')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';

        return $dataTable->render('admin.verifications.address_proofs.list', $data);
    }

    public function addressProofsCsv()
    {
        return Excel::download(new AddressProofsExport(), 'address_proofs_list_' . time() . '.xlsx');
    }

    public function addressProofsPdf()
    {
        $from     = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to       = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status   = isset(request()->status) ? request()->status : null;

        $data['addressProofs'] = $this->addressVerification->getAddressVerificationsList($from, $to, $status)->orderBy('id', 'desc')->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.verifications.address_proofs.address_proofs_report_pdf', 'address_proofs_report_', $data);
    }

    public function addressProofEdit($id)
    {
        $data['menu']     = 'proofs';
        $data['sub_menu'] = 'address-proofs';

        $data['documentVerification'] = DocumentVerification::find($id);
        return view('admin.verifications.address_proofs.edit', $data);
    }

    public function addressProofUpdate(Request $request)
    {
        $documentVerification = DocumentVerification::find($request->id);
        $documentVerification->status = $request->status;
        $documentVerification->save();

        if ($request->verification_type == 'address') {
            $user = User::find($request->user_id);
            $user->address_verified = $request->status == 'approved';
            $user->save();
        }

        $data['status'] = $documentVerification->status;
        $data['type'] = 'Address';

        (new AddressOrIndentityVerificationMailService)->send($user, $data);

        if (!empty($user->formattedPhone)) {
            (new AddressOrIndentityVerificationSmsService)->send($user, $data);
        }

        $this->helper->one_time_message('success', __('The :x has been successfully verified.', ['x' => __('address')]));
        return redirect(config('adminPrefix').'/address-proofs');
    }
}
