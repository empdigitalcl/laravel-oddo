<?php
use Illuminate\Http\Request;

Route::get('oddo/config',
    [
        'as'=>'oddo.config',
        'uses'=>'OddoController@config'
    ]
);
Route::get('oddo',
    [
        'as'=>'oddo.index',
        'uses'=>'OddoController@index'
    ]
);
