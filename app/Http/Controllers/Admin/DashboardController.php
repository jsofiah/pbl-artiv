<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard');
    }

    public function akunDesigner()
    {
        return view('admin.akun.designer');
    }

    public function akunAdmin()
    {
        return view('admin.akun.admin');
    }

    public function akunCustomer()
    {
        return view('admin.akun.customer');
    }

    public function katalog()
    {
        return view('admin.katalog');
    }

    public function hargaExpress()
    {
        return view('admin.harga-express');
    }

    public function file()
    {
        return view('admin.file');
    }

    public function monitoring()
    {
        return view('admin.monitoring');
    }

    public function laporan()
    {
        return view('admin.laporan');
    }

    public function pengaturan()
    {
        return view('admin.pengaturan');
    }
}