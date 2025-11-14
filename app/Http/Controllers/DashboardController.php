<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Jika user sudah login dan terverifikasi, redirect ke homepage
        if (Auth::check() && Auth::user() && Auth::user()->is_verified) {
            return redirect()->route('home');
        }
        
        // Jika belum login atau belum terverifikasi, tampilkan halaman login dashboard
        return view('dashboard.index');
    }
}

