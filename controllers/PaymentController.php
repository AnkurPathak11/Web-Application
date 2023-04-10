<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
class PaymentController extends Controller
{
	   public function actionProcess(array $http_body) {
		  // $http_body = null;
         //print_r ( json_encode($http_body) );
         
        /*  print_r ( 'inside method api call');
		 print_r ( $http_body );
		 die; */
        $http_method = "POST";
        $url = "https://testapi.multisafepay.com/v1/json/orders";
        $ch = curl_init($url);
        
        $request_headers = array(
            "Accept: application/json",
            "api_key:" . "0ff28d5cc3a6e7475be5fa174703788fa155fc94",
        );
        
        //print_r ( json_encode($http_body) );//die;
        if ($http_body !== NULL) {
            $request_headers[] = "Content-Type: application/json";
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($http_body));
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        
        $body = curl_exec($ch);
        
        if($body)
        {
            $responce = json_decode($body);
           
            if($responce->success != "")
            {
                // echo $responce['payment_url'];die;
             header("Location: ".$responce->data->payment_url);exit;
            }
			else{
				print_r ($responce);
                echo "txnid error";
            }
            
            
        }
        if (curl_errno($ch)) {
            throw new \Exception("Unable to communicatie with the MultiSafepay payment server (" . curl_errno($ch) . "): " . curl_error($ch) . ".");
        }
        
        curl_close($ch);
        return $body;
    }

    public function actionPaymentCancel()
    {
        print_r($_REQUEST);
		print_r('failuer=============');
		die;
    }

    public function actionPaymentSuccess()
    {
         print_r($_REQUEST);
		 print_r('success=============');
		 die;
		//return $this->render('success');
    }
	

}