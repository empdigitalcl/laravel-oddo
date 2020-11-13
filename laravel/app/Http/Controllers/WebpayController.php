<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Transbank\Webpay\Configuration;
use Transbank\Webpay\Webpay;
use SoapClient;
use Redirect;
use Mail;
use DB;
use Carbon\Carbon;
use App\WPPosts;
use App\WPPostMeta;
use App\WPUserMeta;
use App\WPWebpayTransactions;
use App\WPSwpmPaymentsTbl;

class WebpayController extends BaseController
{
    const INCREMENT = 12781014;
    private $responseCode = null;
    private $oneClickCommerceCode;
    private $oneClickPrivateKey;
    private $oneClickPublicCert;
    private $oneClickWebpayCert;
    private $oneClickEnvironment;
    private $webpayNormalCommerceCode;
    private $webpayNormalPrivateKey;
    private $webpayNormalPublicCert;
    private $webpayNormalWebpayCert;
    private $webpayNormalEnvironment;
    public function __construct()
    {
        // WEBPAY-NORMAL
        $this->webpayNormalCommerceCode = '597044444405';
        $this->webpayNormalPrivateKey = file_get_contents(base_path() . '/cert/'.$this->webpayNormalCommerceCode.'/'.$this->webpayNormalCommerceCode.'.key');
        $this->webpayNormalPublicCert = file_get_contents(base_path() . '/cert/'.$this->webpayNormalCommerceCode.'/'.$this->webpayNormalCommerceCode.'.crt');
        $this->webpayNormalWebpayCert = file_get_contents(base_path() . '/cert/'.$this->webpayNormalCommerceCode.'/tbk.pem');
        $this->webpayNormalEnvironment = 'INTEGRACION';
        // ONECLICK
        // $this->oneClickCommerceCode = '597035018468';
        $this->oneClickCommerceCode = '597044444405';
        $this->oneClickPrivateKey = file_get_contents(base_path() . '/cert/'.$this->oneClickCommerceCode.'/'.$this->oneClickCommerceCode.'.key');
        $this->oneClickPublicCert = file_get_contents(base_path() . '/cert/'.$this->oneClickCommerceCode.'/'.$this->oneClickCommerceCode.'.crt');
        $this->oneClickWebpayCert = file_get_contents(base_path() . '/cert/'.$this->oneClickCommerceCode.'/tbk.pem');
        $this->oneClickEnvironment = 'INTEGRACION';
        // $this->oneClickEnvironment = 'PRODUCCION';
    }
    private function paymentFailed(SalesOrder $salesOrder, $response)
    {
        return Redirect::to(env('WEBPAY_FAILED') . '/'.$salesOrder->id);
    }
    private function validateTransaction(WPPosts $salesOrder)
    {
        return true;
        /* if (!$salesOrder->paymentStatus && $salesOrder->payment()->count() == 0) {
            return true;
        } else {
            return false;
        } */
    }
    private function authorizateTransaction(WPPosts $salesOrder, $authorizeData)
    {
        $data = [
            'status' => 'completed',
        ];
        // $result = $this->wpConnection('orders/'.$salesOrder->ID, 'PUT', $data);
        $webpayTransaction = WPWebpayTransactions::ByOrderId($salesOrder->ID)->first();
        $webpayTransaction->status = 'approved';
        $webpayTransaction->transbank_response = json_encode($authorizeData);
        $webpayTransaction->save();

        $wpSwpmPaymentsTbl = new WPSwpmPaymentsTbl();
        $wpSwpmPaymentsTbl->email = $salesOrder->customerEmail->meta_value;
        $wpSwpmPaymentsTbl->member_id = $salesOrder->customerUserId->meta_value;
        $wpSwpmPaymentsTbl->reference = $salesOrder->ID;
        $wpSwpmPaymentsTbl->payment_amount = $salesOrder->total->meta_value;
        $wpSwpmPaymentsTbl->txn_date = date('Y-m-d');
        // $wpSwpmPaymentsTbl->first_name = null;
        // $wpSwpmPaymentsTbl->last_name = null;
        // $wpSwpmPaymentsTbl->membership_level = null;
        // $wpSwpmPaymentsTbl->subscr_id = null;
        $wpSwpmPaymentsTbl->txn_id = $webpayTransaction->id;
        $wpSwpmPaymentsTbl->gateway ='oneclick';
        $wpSwpmPaymentsTbl->status ='approved';
        $wpSwpmPaymentsTbl->save();
        /* $data =  [
            'responseCode' => $authorizeData->responseCode,
            'authorizationCode' => $authorizeData->authorizationCode,
            'transactionId' => $authorizeData->transactionId,
            'creditCardType' => $authorizeData->creditCardType,
            'last4CardDigits' => $authorizeData->last4CardDigits,
            'date' => date('Y-m-d')
        ]; */
        /* $salesOrder->payment()->updateOrCreate($data);
        $data = [
            'startDate' => date('Y-m-d'),
            'endDate' => Carbon::parse(date('Y-m-d'))->addMonths($salesOrder->plan->period)
        ];
        $salesOrder->subscription()->updateOrCreate($data);
        $salesOrder->person->showPaymentScreen = 0;
        $salesOrder->person->save();
        $this->notification($salesOrder); */
    }
    private function authorizateTransactionWebpayNormal(SalesOrder $salesOrder, $authorizeData)
    {
        $salesOrder->paymentStatus = true;
        $salesOrder->save();
        $data =  [
            'responseCode' => $authorizeData->responseCode,
            'authorizationCode' => $authorizeData->authorizationCode,
            'date' => date('Y-m-d')
        ];
        $salesOrder->payment()->updateOrCreate($data);
        $data = [
            'startDate' => date('Y-m-d'),
            'endDate' => Carbon::parse(date('Y-m-d'))->addMonths($salesOrder->plan->period+$salesOrder->plan->freePeriod)
        ];
        $salesOrder->subscription()->updateOrCreate($data);
    }
    /////////////////////////////WEBPAYNORMAL///////////////////////////////////
    private function setConfigurationWebpayNormal() {
        if ($this->oneClickEnvironment === 'PRODUCCION') {
            $configuration = new Configuration();
            $configuration->setCommerceCode($this->webpayNormalCommerceCode);
            $configuration->setPrivateKey($this->webpayNormalPrivateKey);
            $configuration->setPublicCert($this->webpayNormalPublicCert);
            $configuration->setWebpayCert($this->webpayNormalWebpayCert);
            $configuration->setEnvironment($this->webpayNormalEnvironment);
            return $configuration;
        } else {
            $configuration = Configuration::forTestingWebpayPlusNormal();
            return $configuration;
        }

    }
    public function testNormalInitTransaction(Request $request)
    {
        if ($request->validator && $request->validator->fails()) {
            return $this->sendError($request->validator->errors());
        }
        $input = $request->all();

        // $salesOrder = WPPosts::find($input['salesOrderId']);

        $transaction = (new Webpay($this->setConfigurationWebpayNormal()))->getNormalTransaction();
        print_r($transaction);
        $amount = 1; //$salesOrder->total;
        // Identificador que será retornado en el callback de resultado:
        $sessionId = 1;
        // Identificador único de orden de compra:
        $buyOrder = 1;
        $returnUrl = env('PUBLIC_API').'/wpay/nor/transaction/result';
        $finalUrl = env('PUBLIC_API').'/wpay/nor/transaction/response';
        $initResult = $transaction->initTransaction($amount, $buyOrder, $sessionId, $returnUrl, $finalUrl);
        /* $salesOrder->webpayToken = $initResult->token;
        $salesOrder->save(); */
        return view("webpay.token", ['url' => $initResult->url, 'token' => $initResult->token, 'type' => 'NORMAL']);
    }
    public function normalInitTransaction(Request $request)
    {
        if ($request->validator && $request->validator->fails()) {
            return $this->sendError($request->validator->errors());
        }
        $input = $request->all();

        $salesOrder = WPPosts::find($input['salesOrderId']);

        $transaction = (new Webpay($this->setConfigurationWebpayNormal()))->getNormalTransaction();
        // print_r($transaction);
        $amount = round($salesOrder->total);
        // Identificador que será retornado en el callback de resultado:
        $sessionId = $salesOrder->personId;
        // Identificador único de orden de compra:
        $buyOrder = $salesOrder->id;
        $returnUrl = env('PUBLIC_API').'/wpay/nor/transaction/result';
        $finalUrl = env('PUBLIC_API').'/wpay/nor/transaction/response';
        $initResult = $transaction->initTransaction($amount, $buyOrder, $sessionId, $returnUrl, $finalUrl);
        // print_r($initResult);
        $salesOrder->webpayToken = $initResult->token;
        $salesOrder->save();
        return view("webpay.token", ['url' => $initResult->url, 'token' => $initResult->token, 'type' => 'NORMAL']);
    }
    public function normalResultTransaction(Request $request)
    {
        $transaction = (new Webpay($this->setConfigurationWebpayNormal()))->getNormalTransaction();
        $result = $transaction->getTransactionResult($request->input("token_ws"));
        $tbkToken = $request->input("token_ws");
        $salesOrder = WPPosts::ByWebpayToken($tbkToken)->first();
        if (isset($result->detailOutput)) {
            if (isset($result->detailOutput->responseCode) && $result->detailOutput->responseCode === 0) {
                $this->authorizateTransactionWebpayNormal($salesOrder, $result->detailOutput);
                return Redirect::to(env('WEBPAY_SUCCESS') . '/'.$salesOrder->id);
            } else {
                $this->responseCode = 22; // responseCode
                return $this->paymentFailed($salesOrder, $result);
            }
        } else {
            $this->responseCode = 22; // responseCode
            return $this->paymentFailed($salesOrder, $result);
        }
        /* $output = $result->detailOutput;
        if ($output->responseCode == 0) {
        } */
    }
    /////////////////////ONE-CLICK//////////////////////////////////
    private function setConfigurationOneClick() {
        // if ($this->oneClickEnvironment === 'PRODUCCION') {
            $configuration = new Configuration();
            $configuration->setCommerceCode($this->oneClickCommerceCode);
            $configuration->setPrivateKey($this->oneClickPrivateKey);
            $configuration->setPublicCert($this->oneClickPublicCert);
            $configuration->setWebpayCert($this->oneClickWebpayCert);
            $configuration->setEnvironment($this->oneClickEnvironment);
            return $configuration;
        /*} else {
            $configuration = Configuration::forTestingWebpayOneClickNormal();
            return $configuration;
        }*/
    }
    //********************BEGIN-TEST*************************** */
    public function testOneClickInitInscription(Request $request, $username)
    {
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $email = $username.'@prevencionline.cl';
        $urlReturn = 'http://localhost:8000/api/wpay/oc/inscription/test/finish';
        $initResult = $transaction->initInscription($username, $email, $urlReturn);
        return view('webpay.token', ['url' => $initResult->urlWebpay, 'token' => $initResult->token, 'type' => 'OC']);
    }
    public function testOneClickFinishInscription(Request $request)
    {
        $tbkToken = $request->input('TBK_TOKEN');
        echo $tbkToken.'<br>';
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $result = $transaction->finishInscription($tbkToken);
        var_dump($result);
    }
    public function testOneClickAuthorize($tbkUser, $user)
    {
        $buyOrder = static::INCREMENT+rand(1000,9999);
        echo 'buyOrder='.$buyOrder.'<br>';
        $username = $user; // El mismo usado en initInscription.
        $amount = 12000111;
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $output = $transaction->authorize($buyOrder, $tbkUser, $username, $amount);
        var_dump($output);
    }
    public function testOneClickReverseTransaction(Request $request, $buyOrder)
    {
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $output = $transaction->reverseTransaction($buyOrder);
        var_dump($output);
    }
    public function testOneClickRemoveUser(Request $request, $tbkUser, $username)
    {
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $output = $transaction->removeUser($tbkUser, $username);
        var_dump($output);
    }
    public function test() {
        echo 'xxxx<br>';
        echo env('WEBPAY_FAILED').'<br>';
        echo env('WEBPAY_SUCCESS').'<br>';
        echo env('PUBLIC_API').'<br>';
        echo 'base_path: '.base_path().'<br>';
        $path = base_path() . '/cert/'.$this->webpayNormalCommerceCode.'/'.$this->webpayNormalCommerceCode.'.key';
        if (file_get_contents($path)) {
            echo 'encontrado '.$path.'<br>';
        } else {
            echo 'NO ENCONTRADO '.$path.'<br>';
        }
        print_r($this->setConfigurationOneClick());
    }
    //********************END-TEST*************************** */
    public function oneClickInitInscription(Request $request)
    {
        if ($request->validator && $request->validator->fails()) {
            return $this->sendError($request->validator->errors());
        }
        $input = $request->all();
        $salesOrder = WPPosts::find($input['salesOrderId']);
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $username = 'minibook-'.$salesOrder->customerUserId->meta_value;
        $email = $salesOrder->customerEmail->meta_value;
        $urlReturn = env('PUBLIC_API'). '/wpay/oc/inscription/finish';
        $initResult = $transaction->initInscription($username, $email, $urlReturn);
        $webpayTransaction = WPWebpayTransactions::create([
            'order_id' => $salesOrder->ID,
            'buy_order' => $salesOrder->ID,
            'amount' => $salesOrder->total->meta_value,
            'session' => uniqid(13),
            'status' => 'pending',
            'token' => $initResult->token,
        ]);
        $salesOrderMeta = WPPostMeta::create([
            'post_id' => $salesOrder->ID,
            'meta_key' => '_webpay_token',
            'meta_value' => $initResult->token,
        ]);
        return view('webpay.token', ['url' => $initResult->urlWebpay, 'token' => $initResult->token, 'type' => 'OC']);
    }
    public function oneClickFinishInscription(Request $request)
    {
        $tbkToken = $request->input('TBK_TOKEN');
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $result = $transaction->finishInscription($tbkToken);
        if (isset($result->responseCode)) {
            $webpayTransaction = WPWebpayTransactions::ByToken($tbkToken)->first();
            $salesOrder = WPPosts::find($webpayTransaction->order_id);
            if ($result->responseCode === 0) {
                $user = WPUserMeta::create([
                    'user_id' => $salesOrder->customerUserId->meta_value,
                    'meta_key' => 'webpay_tbk_user',
                    'meta_value' => Crypt::encryptString($result->tbkUser),
                ]);
                // dd($result);
                // $result->authCode
                // $result->creditCardType
                // $result->last4CardDigits
                return $this->oneClickInitTransactionAfterInscription($salesOrder);
            } else {
                $this->responseCode = 22; // responseCode
                return $this->paymentFailed($salesOrder, $result);
            }
        } else {
            $this->responseCode = 21; // result=null
            return $this->paymentFailed($salesOrder, $result);
        }
    }
    private function oneClickInitTransactionAfterInscription(WPPosts $salesOrder)
    {
        $buyOrder = static::INCREMENT + $salesOrder->ID;
/*         echo $salesOrder->customerUserId->meta_value.'<br>';
        DB::enableQueryLog();
        $userMeta = WPUserMeta::ByUserId($salesOrder->customerUserId->meta_value)->ByMetaKey('webpay_tbk_user')->first();
        $query = DB::getQueryLog();
        print_r($query);
        echo $userMeta->meta_value.'<br>';
        die(); */
        $tbkUser = $this->getTbkUser($salesOrder->customerUserId->meta_value); // '5814fdec-53d8-45cb-8f20-c0c3ead86b30';
        $username = 'minibook-'.$salesOrder->customerUserId->meta_value; // El mismo usado en initInscription.
        $amount = round($salesOrder->total->meta_value);
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $output = $transaction->authorize($buyOrder, $tbkUser, $username, $amount);
        print_r($output);
        if (isset($output->responseCode) && $output->responseCode === 0 && $this->validateTransaction($salesOrder)) {
            $this->authorizateTransaction($salesOrder, $output);
            // print_r($output);
            echo 'OK';
            echo env('WEBPAY_SUCCESS') . '/'.$salesOrder->id.'?bo='.$buyOrder;
            // return Redirect::to(env('WEBPAY_SUCCESS') . '/'.$salesOrder->id.'?bo='.$buyOrder);
            // authCode
            // creditCardType
            // last4CardDigits
        } else {
            echo 'FAILED';
            $this->responseCode = 20; // validación
            // return $this->paymentFailed($salesOrder, $output);
        }
    }
    public function getTbkUser($userId)
    {
        $userMeta = WPUserMeta::ByUserId($userId)->ByMetaKey('webpay_tbk_user')->first();
        return Crypt::decryptString($userMeta->meta_value);
    }
    public function oneClickInitTransaction(Request $request)
    {
        // dd($this->setConfigurationOneClick());
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $buyOrder = rand(100000, 999999999);
        $tbkUser = '5814fdec-53d8-45cb-8f20-c0c3ead86b30';
        $username = "ricardo"; // El mismo usado en initInscription.
        $amount = 11000000;
        // echo $buyOrder.'<br>';
        $output = $transaction->authorize($buyOrder, $tbkUser, $username, $amount);
        if (isset($output->responseCode) && $output->responseCode === 0) {
            var_dump($output);
        } else {
            var_dump($output);
        }
    }
    public function oneClickReverseTransaction(Request $request, $buyOrder)
    {
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $output = $transaction->reverseTransaction($buyOrder);
        // var_dump($output);
    }
    public function oneClickRemoveUser(Request $request, $personId)
    {
        // var_dump($request->all());
        $person = Person::find($personId);
        $transaction = (new Webpay($this->setConfigurationOneClick()))->getOneClickTransaction();
        $tbkUser = Crypt::decryptString($person->tbkUser);
        $username = 'prevo'.$person->id;
        // echo 'tbkUser: '.$tbkUser.' username: '.$username.'<br>';
        $output = $transaction->removeUser($tbkUser, $username);
        // var_dump($output);
    }
    public function sendNotification($salesOrderId) {
        $salesOrder = WPPosts::find($salesOrderId);
        $this->notification($salesOrder);
    }
    private function notification($salesOrder)
    {
        $body = 'Monto: $'.number_format($salesOrder->total, 0, ',', '.');
        $body.= '<br>Fecha: '.$salesOrder->date;
        if ($salesOrder->plan) {
            $body.='<br>'.$salesOrder->plan->description;
        }
        if ($salesOrder->paymentMethod) {
            $body.= '<br>Forma de pago: '.$salesOrder->paymentMethod->name;
        }
        if ($salesOrder->payments) {
            foreach ($salesOrder->payments as $payment) {
                $body.= '<br>Código de autorización: '.$payment->authorizationCode;
            }
        }
        if ($salesOrder->subscription) {
            $body.='<br>Suscripción:';
            $body.= '<br>Fecha inicio: '.$salesOrder->subscription->startDate;
            $body.= '<br>Fecha término: '.$salesOrder->subscription->endDate;
        }
        $data = [
            'titleH1' => 'Comprobante de pago Prevencionline!',
            'fullName' => '',
            'titleH3' => 'Se ha recibido el pago N '.$salesOrder->id.':',
            'body' => $body,
            'buttonText' => null,
            'buttonLink' => null
        ];
        $subject = 'Comprobante de pago Prevencionline';
        $emailBody = view('notifications.voucher', $data)->render();
        try {
            Mail::send([], [], function ($message) use ($subject, $salesOrder, $emailBody) {
                $message->from('noreply@prevencionline.com');
                $message->subject($subject);
                $message->setBody($emailBody, 'text/html');
                $message->to($salesOrder->person->email);
                $message->bcc(['ventas@prevencionline.com', 'ricardo@empdigital.cl']);
            });
        } catch (\Swift_TransportException $e) {
        }
    }
    public function wpConnection ($function='orders', $method='GET', $data=array())
    {
        $response = null;
        if ($function!=null) {
            $url = env('WOOCOMMERCE_STORE_URL').'/'.$function.'?consumer_key='.env('WOOCOMMERCE_CONSUMER_KEY') . '&consumer_secret=' . env('WOOCOMMERCE_CONSUMER_SECRET');
            $session = curl_init($url);
            $headers = array(
                'Accept: application/json',
                'Content-Type: application/json'
            );
            $config = array(
                CURLOPT_URL => $url,
                CURLOPT_USERPWD => env('WOOCOMMERCE_CONSUMER_KEY') . ":" . env('WOOCOMMERCE_CONSUMER_SECRET'),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30000,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_SSL_VERIFYHOST => 0,
                CURLOPT_SSL_VERIFYPEER => 0
            );
            if (count($data)>0){
                $config[CURLOPT_POSTFIELDS] = json_encode($data);
            }
            // print_r($config);
            curl_setopt_array($session, $config);
            $response = curl_exec($session);
            $err = curl_error($session);
            $code = curl_getinfo($session, CURLINFO_HTTP_CODE);
            curl_close($session);
            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                $response = json_decode($response);
            }
        }
        return $response;
    }
}
