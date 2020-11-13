<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Sync;

class OddoController extends Controller
{
    //
    public function index(){
        $odoo = new \Edujugon\Laradoo\Odoo();
        $version = $odoo->version();
        var_dump($version);
        $userId= $odoo->getUid();
        echo 'UserId: '.$userId.'';
        /* $ids = $odoo->where('customer', '=', true)
            ->search('res.partner');
        var_dump($ids); */
    }
    
}
