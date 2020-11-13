<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Woocommerce;
use App\WPUserMeta;

class UserController extends Controller
{
    //
    public function index(){
        $result = WPUserMeta::get();
        var_dump($result);
    }
    public function config(){
        return 'config';
    }
    
}
