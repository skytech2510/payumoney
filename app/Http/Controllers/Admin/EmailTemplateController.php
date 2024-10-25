<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use Illuminate\Http\Request;
use Common;

class EmailTemplateController extends Controller
{
    public function index($alias = null)
    {
        $data['menu'] = 'templates';
        $data['sub_menu'] = 'email_template';

        $data['emailTemplates'] = [];
        EmailTemplate::where(['type' => 'email', 'status' => 'Active'])
                ->get(['id', 'name', 'alias', 'type', 'lang', 'group', 'status'])
                ->map(function($templates) use (&$data) {
                    return $data['emailTemplates'][$templates->group][$templates->name][$templates->lang] = $templates;
                });

        $data['templateAlias'] = $alias ?? $data['emailTemplates']['Deposit']['Notify Admin on Deposit']['en']['alias'];
        $data['templateData'] = EmailTemplate::where(['type' => 'email', 'status' => 'Active', 'alias' => $data['templateAlias']])->get(['id', 'subject', 'body']);

        return view('admin.email_templates.index', $data);
    }

    public function update(Request $request, $alias)
    {
        $data[] = $request->en;
        $data[] = $request->ar;
        $data[] = $request->fr;
        $data[] = $request->pt;
        $data[] = $request->ru;
        $data[] = $request->es;
        $data[] = $request->tr;
        $data[] = $request->ch;

        $array = $data;
        array_unshift($array, "");
        unset($array[0]);

        // Templates will be available for only 8 languages
        for ($i = 1; $i < 9; $i++) {
            EmailTemplate::where([
                'alias'     => $alias,
                'type'     => 'email',
                'language_id' => $i,
            ])->update($array[$i]);
        }

        (new Common)->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('email template')]));

        return redirect()->route('email.template.index', $alias);
    }
}
