<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Woocommerce;
use App\WPPosts;

class OrderController extends Controller
{
    //
    public function index(){
        $orders = WPPosts::Orders()->get();
        if ($orders->count() > 0) {
            foreach ($orders as $order) {
                echo $order->ID.'<br>';
                echo '<br>=======ORDER-VALUES=======</br>';
                if ($order->total) {
                    $order->total->meta_value;
                }
                // echo $order->total->meta_value;
                echo '<br>=======ORDER-ITEM=======</br>';
                var_dump($order->items);
                if ($order->items->count() > 0) {
                    foreach ($order->items as $item) {
                        echo '<br>======META-VALUES========</br>';
                        var_dump($item->metaValues);
                    }
                }
            }
        }
    }
    public function config(){
        /* echo 'user = '.env('LAUDUS_USER').'<br>';
        echo 'password = '.env('LAUDUS_PASSWORD').'<br>'; */
        echo 'WOOCOMMERCE_STORE_URL = '.env('WOOCOMMERCE_STORE_URL').'<br>';
        echo 'WOOCOMMERCE_CONSUMER_KEY = '.env('WOOCOMMERCE_CONSUMER_KEY').'<br>';
        echo 'WOOCOMMERCE_CONSUMER_SECRET = '.env('WOOCOMMERCE_CONSUMER_SECRET').'<br>';
        echo 'WOOCOMMERCE_VERIFY_SSL = '.env('WOOCOMMERCE_VERIFY_SSL').'<br>';
        echo 'WOOCOMMERCE_VERSION = '.env('WOOCOMMERCE_VERSION').'<br>';
        echo 'WOOCOMMERCE_WP_API = '.env('WOOCOMMERCE_WP_API').'<br>';
        echo 'WOOCOMMERCE_WP_QUERY_STRING_AUTH = '.env('WOOCOMMERCE_WP_QUERY_STRING_AUTH').'<br>';
        echo 'WOOCOMMERCE_WP_TIMEOUT = '.env('WOOCOMMERCE_WP_TIMEOUT').'<br>';
        echo 'PUBLIC_API = '.env('PUBLIC_API').'<br>';
        echo 'WEBPAY_FAILED = '.env('WEBPAY_FAILED').'<br>';
        echo 'WEBPAY_SUCCESS = '.env('WEBPAY_SUCCESS').'<br>';
    }
    public function getOrders(){
        $data = [
            'status' => 'completed',
            /* 'filter' => [
                'created_at_min' => '2020-01-01'
            ] */
        ];
        /* $result = Woocommerce::get('orders', $data);
        print_r($result); */
        // $result = Woocommerce::get('orders', $data);
        $result = $this->wpConnection('orders/1313', 'PUT', $data);
        dd($result);
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
