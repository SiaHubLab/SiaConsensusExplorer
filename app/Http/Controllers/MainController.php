<?php

namespace App\Http\Controllers;

use App\Hash;
use App\HashType;
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
    public function index($hash_id = false)
    {
        $title = false;
        if($hash_id) {
            $hash = Hash::where('hash', $hash_id)->first();
            if($hash) {
                $title = sprintf("%s - %s", $hash->hash, (new HashType($hash->type))->title());
            }
        }
        return view('main', [
            'title' => $title
        ]);
    }
}
