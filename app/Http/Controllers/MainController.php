<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;

class MainController extends BaseController
{
    /**
     * Main explorer page
     *
     * Show main page
     *
     * @return return view main
     */
    public function index()
    {
        return view('main');
    }
}
