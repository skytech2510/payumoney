<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ModuleManagerController extends Controller
{
    public function index()
    {
        $data['menu'] = 'addon-manager';
        return view('admin.module_manager.addon', $data);
    }
}
