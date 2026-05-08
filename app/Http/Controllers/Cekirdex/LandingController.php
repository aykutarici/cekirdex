<?php

namespace App\Http\Controllers\Cekirdex;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        return view('cekirdex.landing');
    }

    public function pricing()
    {
        return view('cekirdex.pricing');
    }

    public function forRestaurants()
    {
        return view('cekirdex.for-restaurants');
    }

    public function forGuests()
    {
        return view('cekirdex.for-guests');
    }

    public function contact()
    {
        return view('cekirdex.contact');
    }

    public function privacy()
    {
        return view('cekirdex.privacy');
    }

    public function terms()
    {
        return view('cekirdex.terms');
    }
}
