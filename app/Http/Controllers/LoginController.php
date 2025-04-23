<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function index(){
        return view('pages.login');
    }

    public function login(Request $request)
    {
        $request -> validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        
        if(Auth::attempt($request->only('email', 'password'))) {
            return redirect()->intended('/admin/dashboard');
        }

        return back()->withErrors(['email' => 'Email atau password salah'])->withInput();
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }
}
