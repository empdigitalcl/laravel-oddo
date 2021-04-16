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

        // dd($version);
        $userId = $odoo->getUid();
        echo 'UserId: '.$userId.'';
        $ids = $odoo->limit(30)->fields('default_code','cantidad_disponible_bodega_temple')->get('product.template');
        dd($ids);
    }
    
}
