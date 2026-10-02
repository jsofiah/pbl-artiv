<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('designer.dashboard');
    }

    public function jobPool()
    {
        return view('designer.job-pool');
    }

    public function pekerjaanSaya()
    {
        return view('designer.pekerjaan-saya');
    }

    public function notifikasi()
    {
        return view('designer.notifikasi');
    }

    public function riwayat()
    {
        return view('designer.riwayat');
    }
}