<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;

class BerandaController extends Controller
{
    public function index()
    {
        return view('customer.beranda');
    }

    public function katalog()
    {
        return view('customer.katalog');
    }

    public function pesanan()
    {
        return view('customer.pesanan');
    }
}