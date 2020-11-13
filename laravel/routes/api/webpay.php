<?php
use Illuminate\Http\Request;

Route::get('wpay/test/nor/transaction/init',
    [
        'as'=>'webpay.testNormalInitTransaction',
        'uses'=>'WebpayController@testNormalInitTransaction'
    ]
);
Route::get('wpay/nor/transaction/init',
    [
        'as'=>'webpay.normalInitTransaction',
        'uses'=>'WebpayController@normalInitTransaction'
    ]
);
Route::post('wpay/nor/transaction/result',
    [
        'as'=>'webpay.normalResultTransaction',
        'uses'=>'WebpayController@normalResultTransaction'
    ]
);

Route::get(
    'wpay/oc/inscription/test',
    [
        'as' => 'webpay.oneclick.inscription.test',
        'uses'=>'WebpayController@test'
    ]
);
// ****************** INICIO ONECLICK TEST ****************
Route::get(
    'wpay/oc/inscription/test/init/{username?}',
    [
        'as' => 'webpay.oneclick.inscription.test.init',
        'uses'=>'WebpayController@testOneClickInitInscription'
    ]
);
Route::post(
    'wpay/oc/inscription/test/finish',
    [
        'as' => 'webpay.oneclick.inscription.test.finish',
        'uses'=>'WebpayController@testOneClickFinishInscription'
    ]
);
Route::get(
    'wpay/oc/test/authorize/{tbkuser?}/{username?}',
    [
        'as' => 'webpay.oneclick.test.authorize',
        'uses'=>'WebpayController@testOneClickAuthorize'
    ]
);
Route::get(
    'wpay/oc/test/reverse/{buyOrder?}',
    [
        'as' => 'webpay.oneclick.test.reverse',
        'uses'=>'WebpayController@testOneClickReverseTransaction'
    ]
);
Route::get(
    'wpay/oc/test/remove/{tbkuser?}/{username?}',
    [
        'as' => 'webpay.oneclick.test.remove',
        'uses'=>'WebpayController@testOneClickRemoveUser'
    ]
);

// ****************** FIN ONECLICK TEST ****************
Route::get(
    'wpay/oc/inscription/init',
    [
        'as' => 'webpay.oneclick.inscription.init',
        'uses'=>'WebpayController@oneClickInitInscription'
    ]
);
Route::post(
    'wpay/oc/inscription/finish',
    [
        'as' => 'webpay.oneclick.inscription.finish',
        'uses'=>'WebpayController@oneClickFinishInscription'
    ]
);
Route::get('wpay/oc/transaction/init',
    [
        'as' => 'webpay.oneclick.transaction.init',
        'uses'=>'WebpayController@oneClickInitTransaction'
    ]
);
Route::get(
    'wpay/oc/transaction/reverse/{id}',
    [
        'as' => 'webpay.oneclick.transaction.reverse',
        'uses'=>'WebpayController@oneClickReverseTransaction'
    ]
);
Route::get(
    'wpay/oc/tbkuser/{id}',
    [
        'as' => 'webpay.oneclick.tbkuser',
        'uses'=>'WebpayController@getTbkUser'
    ]
);
Route::get(
    'wpay/oc/tbkuser/remove/{id}',
    [
        'as' => 'webpay.oneclick.user.remove',
        'uses'=>'WebpayController@oneClickRemoveUser'
    ]
);
Route::get(
    'wpay/notification/{id}',
    [
        'as' => 'webpay.notificacion',
        'uses'=>'WebpayController@sendNotification'
    ]
);
