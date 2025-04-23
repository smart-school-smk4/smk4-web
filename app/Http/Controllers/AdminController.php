<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index(){
        session(['admin_active' => true]);

        return view('admin.dashboard');
    }
}
