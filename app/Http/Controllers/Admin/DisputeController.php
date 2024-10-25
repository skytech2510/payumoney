<?php

namespace App\Http\Controllers\Admin;

use App\Services\Mail\Dispute\DisputeReplyMailService;
use App\Http\Controllers\Users\EmailController;
use App\DataTables\Admin\DisputesDataTable;
use Validator, Auth, DB, Common, Exception;
use App\Http\Controllers\Controller;
use App\Rules\CheckValidFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\{Dispute,
    DisputeDiscussion,
    Transaction,
    Reason,
    User
};

class DisputeController extends Controller
{
    protected $helper;
    protected $email;
    protected $dispute;

    public function __construct()
    {
        $this->helper  = new Common();
        $this->email   = new EmailController();
        $this->dispute = new Dispute();
    }

    public function index(DisputesDataTable $dataTable)
    {
        $data['menu']     = 'dispute';
        $data['sub_menu'] = 'dispute';

        $data['summary'] = Dispute::select('id', 'status')->addSelect(DB::raw('COUNT(status) as total_status'))->groupBy('status')->get();
        $data['dispute_status'] = $this->dispute->select('status')->groupBy('status')->get();
        $data['dispute_list'] = Dispute::orderBy('id', 'desc')->get();

        $data['from'] = isset(request()->from) ? setDateForDb(request()->from) : null;
        $data['to'] = isset(request()->to ) ? setDateForDb(request()->to) : null;
        $data['status'] = isset(request()->status) ? request()->status : 'all';
        $data['user'] = $user = isset(request()->user_id) ? request()->user_id : null;
        $data['getName'] = $this->dispute->getDisputesUsersName($user);

        return $dataTable->render('admin.dispute.list', $data);
    }

    public function disputesUserSearch(Request $request)
    {
        $search = $request->search;
        $users  = $this->dispute->getDisputesUsersResponse($search);

        $res = [
            'status' => 'fail',
        ];
        if (count($users) > 0)
        {
            $i = 0;

            foreach ($users as $key => $value)
            {
                $array[$i]['id']         = $value->id;
                $array[$i]['first_name'] = $value->first_name;
                $array[$i]['last_name']  = $value->last_name;
                $i++;
            }
            $res = [
                'status' => 'success',
                'data'   => $array,
            ];
        }
        return json_encode($res);
    }

    public function add($id)
    {
        $data['menu']        = 'dispute';
        $data['sub_menu']    = 'dispute';
        $data['transaction'] = Transaction::find($id);
        $data['reasons'] = Reason::all();

        return view('admin.dispute.add', $data);
    }

    public function store(Request $request)
    {
        $rules = array(
            'title'       => 'required',
            'description' => 'required',
        );

        $fieldNames = array(
            'title'       => 'Title',
            'description' => 'Description',
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails())
        {
            return back()->withErrors($validator)->withInput();
        }
        else
        {
            $dispute                 = new Dispute();
            $dispute->claimant_id    = $request->claimant_id;
            $dispute->defendant_id   = $request->defendant_id;
            $dispute->transaction_id = $request->transaction_id;
            $dispute->reason_id      = $request->reason_id;
            $dispute->title          = $request->title;
            $dispute->description    = $request->description;
            $dispute->code           = 'DIS-' . strtoupper(Str::random(6));
            $dispute->save();
            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('dispute')])
        );
            return redirect(config('adminPrefix').'/disputes');
        }
    }

    public function discussion($id)
    {
        $data['menu'] = 'dispute';
        $data['sub_menu'] = 'dispute';
        $data['dispute'] = Dispute::find($id);

        return view('admin.dispute.discussion', $data);
    }

    public function storeReply(Request $request)
    {
        $rules = array(
            'description' => 'required',
            'file' => [new CheckValidFile(getFileExtensions(1), true)],
        );

        $fieldNames = array(
            'description' => 'Message',
            'file'        => __('File'),
        );

        $validator = Validator::make($request->all(), $rules);
        $validator->setAttributeNames($fieldNames);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        } else {
            $file = $request->file('file');

            if (isset($file)) {
                $fileName = time() . '_' . $file->getClientOriginalName();

                $path = 'uploads\files';
                $destinationPath = public_path($path);

                try {
                    $file->move($destinationPath, $fileName);
                } catch (Exception $e) {
                    $this->helper->one_time_message('error', $e->getMessage());
                    return redirect()->back();
                }
            }

            $discussion             = new DisputeDiscussion();
            $discussion->user_id    = auth('admin')->user()->id;
            $discussion->message    = $request->description;
            $discussion->dispute_id = $request->dispute_id;
            $discussion->file       = isset($fileName) ? $fileName : null;
            $discussion->type       = 'Admin';
            $discussion->save();

            // Notification email/SMS
            (new DisputeReplyMailService)->send($discussion, [
                'recipient' => User::find($discussion?->dispute?->claimant?->id, ['first_name', 'last_name', 'email']),
                'replier' => getColumnValue(auth()->guard('admin')->user())
            ]);
            (new DisputeReplyMailService)->send($discussion, [
                'recipient' => User::find($discussion?->dispute?->defendant->id, ['first_name', 'last_name', 'email']),
                'replier' => getColumnValue(auth()->guard('admin')->user())
            ]);

            $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('dispute reply')]));

            return redirect(config('adminPrefix') . '/dispute/discussion/' . $discussion->dispute_id);
        }
    }

    public function changeReplyStatus(Request $request)
    {
        $dispute         = Dispute::find($request->id);
        $dispute->status = $request->status;
        $dispute->save();

        $data['status'] = 1;

        return json_encode($data);
    }

    public function download($file_name)
    {
        $file_path = public_path('/uploads/files/' . $file_name);
        return response()->download($file_path);
    }
}
