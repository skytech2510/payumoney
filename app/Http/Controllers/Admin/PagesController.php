<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\PagesDataTable;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\Controller;
use Validator, Config, Common;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use DOMDocument;
use App\Models\{Meta,
    Pages
};

class PagesController extends Controller
{
    protected $helper;
    public function __construct()
    {
        $this->helper = new Common();
    }
    protected $data = [];

    public function index(PagesDataTable $dataTable)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'pages';
        return $dataTable->render('admin.pages.list', $data);
    }

    public function add()
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'pages';
        return view('admin.pages.add', $data);
    }

    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'    => 'required|unique:pages,name',
            'content' => 'required',
        ]);

        if ($validation->fails())
        {
            return redirect()->back()->withErrors($validation->errors());
        }

        $position = [];
        if ($request->header)
        {
            $position[] = 'header';
        }
        if ($request->footer)
        {
            $position[] = 'footer';
        }

        $page           = new Pages();
        $page->name     = trim($request->name);
        $page->url      = Str::slug(trim($request->name), '-');
        $page->status   = $request->status;
        $page->position = $position;

        $content = $request->content;

        $dom = new DomDocument();
        libxml_use_internal_errors(true);

        // $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        // $dom->loadHtml($content);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8')); //to UTF-8

        libxml_use_internal_errors(false);
        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img)
        {
            $src = $img->getAttribute('src');
            // if the img source is 'data-url'
            if (preg_match('/data:image/', $src))
            {
                // get the mimetype
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];
                // Generating a random filename
                $filename = uniqid();
                $filepath = "/uploads/page-images/$filename.$mimetype";
                $image    = Image::make($src)
                    ->encode($mimetype, 100)
                    ->save(public_path($filepath));
                $new_src = url("public/uploads/page-images/$filename.$mimetype");
                $img->removeAttribute('src');
                $img->setAttribute('src', $new_src);
            }
        }
        $page->content = $dom->saveHTML();
        $page->save();

        $meta              = new Meta();
        $meta->url         = $page->url;
        $meta->title       = $page->name;
        $meta->description = $page->name;
        $meta->keywords    = '';
        $meta->save();

        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('page')]));
        return redirect()->intended(config('adminPrefix')."/settings/pages");
    }

    public function edit($page_id)
    {
        $data['menu'] = 'settings';
        $data['settings_menu'] = 'pages';
        $data['page'] = $page = Pages::find($page_id);

        return view('admin.pages.edit', $data);
    }

    public function update(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'    => 'required|unique:pages,name,' . $request['id'],
            'content' => 'required',
        ]);

        if ($validation->fails())
        {
            return redirect()->back()->withErrors($validation->errors());
        }

        $position = [];
        if ($request->header)
        {
            $position[] = 'header';
        }
        if ($request->footer)
        {
            $position[] = 'footer';
        }

        $page           = Pages::find($request['id']);
        $page->name     = trim($request->name);
        $page->url      = Str::slug(trim($request->name), '-'); //fixed - pm v2.3
        $page->status   = $request->status;
        $page->position = $position;
        $content = $request->content;

        $dom = new DomDocument();
        libxml_use_internal_errors(true);

        // $dom->loadHtml($content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        // $dom->loadHtml($content);
        $dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8')); //to UTF-8

        libxml_use_internal_errors(false);
        $images = $dom->getElementsByTagName('img');
        foreach ($images as $img)
        {
            $src = $img->getAttribute('src');
            if (preg_match('/data:image/', $src))
            {
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];
                // Generating a random filename
                $filename = uniqid();
                $filepath = public_path("uploads/page-images/$filename.$mimetype");
                $image    = Image::make($src)
                    ->encode($mimetype, 100)
                    ->save($filepath);
                $new_src = url("public/uploads/page-images/$filename.$mimetype");
                $img->removeAttribute('src');
                $img->setAttribute('src', $new_src);
            }
        }
        $page->content = $dom->saveHTML();
        $page->save();

        $this->helper->one_time_message('success', __('The :x has been successfully saved.', ['x' => __('page')]));
        return redirect()->intended(config('adminPrefix')."/settings/pages");
    }

    public function delete($page_id)
    {
        $page = Pages::find($page_id);
        $meta = Meta::where('url', $page->url)->first();
        if ($meta)
        {
            $meta->delete();
        }
        $page->delete();
        $this->helper->one_time_message('success', __('The :x has been successfully deleted.', ['x' => __('page')]));
        return redirect()->intended(config('adminPrefix')."/settings/pages");
    }
}
