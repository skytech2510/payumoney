<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\IdentityProofsDataTable;
use App\Http\Controllers\Users\EmailController;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Models\{DocumentVerification,
    User
};
use App\Exports\IdentityProofsExport;
use App\Services\Mail\AddressOrIndentityVerificationMailService;
use App\Services\Sms\AddressOrIndentityVerificationSmsService;

class IdentityProofController extends Controller
{
    protected $helper;
    protected $documentVerification;
    protected $email;

    public function __construct()
    {
        $this->helper               = new Common();
        $this->documentVerification = new DocumentVerification();
        $this->email                = new EmailController();
    }

    public function index(IdentityProofsDataTable $dataTable)
    {
        $data['menu']     = 'proofs';
        $data['sub_menu'] = 'identity-proofs';

        $data['documentVerificationStatus'] = $this->documentVerification->where(['verification_type' => 'identity'])->select('status')->groupBy('status')->get();

        $data['from']     = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to']       = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status']   = isset(request()->status) ? request()->status : 'all';

        return $dataTable->render('admin.verifications.identity_proofs.list', $data);
    }

    public function identityProofsCsv()
    {
        return Excel::download(new IdentityProofsExport(), 'identity_proofs_list_' . time() . '.xlsx');
    }

    public function identityProofsPdf()
    {
        $from = !empty(request()->startfrom) ? setDateForDb(request()->startfrom) : null;
        $to = !empty(request()->endto) ? setDateForDb(request()->endto) : null;
        $status = isset(request()->status) ? request()->status : null;

        $data['identityProofs'] = $this->documentVerification->getDocumentVerificationsList($from, $to, $status)->orderBy('id', 'desc')->get();

        $data['date_range'] = (isset($from) && isset($to)) ? $from . ' To ' . $to : 'N/A';

        generatePDF('admin.verifications.identity_proofs.identity_proofs_report_pdf', 'identity_proofs_report_', $data);
    }

    public function identityProofEdit($id)
    {
        $data['menu']     = 'proofs';
        $data['sub_menu'] = 'identity-proofs';

        $data['documentVerification'] = DocumentVerification::find($id);

        return view('admin.verifications.identity_proofs.edit', $data);
    }

    public function identityProofUpdate(Request $request)
    {
        $documentVerification         = DocumentVerification::find($request->id);
        $documentVerification->status = $request->status;
        $documentVerification->save();

        if ($request->verification_type == 'identity') {
            $user = User::find($request->user_id);
            $user->identity_verified = $request->status == 'approved';
            $user->save();

            $data['status'] = $documentVerification->status;
            $data['type'] = 'Identity';

            (new AddressOrIndentityVerificationMailService)->send($user, $data);

            if (!empty($user->formattedPhone)) {
                (new AddressOrIndentityVerificationSmsService)->send($user, $data);
            }
        }

        $this->helper->one_time_message('success', __('The :x has been successfully verified.', ['x' => __('identity')]));
        return redirect(config('adminPrefix').'/identity-proofs');
    }
}
