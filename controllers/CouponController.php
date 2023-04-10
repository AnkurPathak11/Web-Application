<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\Coupon;
use app\models\Clients;
use app\models\LoginForm;
use yii\helpers\ArrayHelper;
use app\models\CaravanDetails;

class CouponController extends Controller
{

	public function actionNew()
	{
		
		$model = new Coupon();
		if(Yii::$app->user->isGuest) {
           $session=Yii::$app->session;
           $session->set('url',$_SERVER['REQUEST_URI']);
           return $this->redirect(['register/login']);  
        }
		$getRolesByUser=Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
		if(is_array($getRolesByUser))
		$role=array_keys($getRolesByUser)[0];
		 if(strcmp($role,'customer')==0){
		Yii::$app->session->setFlash('error', 'you are not authorised');
		return $this->redirect(['/site/unauthorised']);
		$this->partialrender('dashboard');	
		}
		
			return $this->render('generatecoupon',['model'=>$model]);
		
		
	}
	
	public function actionSave()
	{
	$model = new Coupon();
	
	if(isset($_POST['Coupon']))
     {
         $model->attributes = $_POST['Coupon'];
		 $model->save(false);
		 $this->redirect(array('coupon/new'));
         
         
    }
   	
	} 
	
	public function actionUniquecouponcodeid(){
	$code = "UNI".substr(date("d").date("m").date("Y"),2,2).date("his");
	$code .= mt_rand(11111,99999);
	echo $code;
	}
	public function actionCouponcodediscount(){
	$Coupondetails=new Coupon();
	$clientsdetail=new Clients();
	$clientsdetail=Clients::find()->all();
	$currentdate=date("y-m-d H:m:s");
	$clientId=Yii::$app->user->identity->UserId;
	
	$clientsdetail = Clients::find()->where(['UserId' => $clientId])->exists();
	if($clientsdetail){
		if (Yii::$app->request->post()){
		$data =  Yii::$app->request->post();
		if(Coupon::find()->where(['Name' =>$data['couponame'] ])->exists()){
		$Coupondetails = Coupon::find()->where(['Name' =>$data['couponame'] ])->one();
		if($Coupondetails->Name===$data['couponame']){
			$Ccode=$Coupondetails->Code;
			$CExpiry=$Coupondetails->ExpireDate;
			$CCreated=$Coupondetails->CreatedOn;
			$CAmount=$Coupondetails->AmountOffer;
			$CStatus=$Coupondetails->Status;
			$CCouponTypeUser=$Coupondetails->CouponTypeUser;
			$coupontypeoffer=$Coupondetails->CouponTypeOffer;
		
		if(isset($Ccode) && $currentdate<$CExpiry && $currentdate<$CCreated && $CStatus==1)
		{
			
			if($coupontypeoffer =='single_user'){
				$this->$CStatus="1";
			}
			else if($coupontypeoffer =='Percentage')
			{
				$totalPrice=$data['price'];
				$discount=($totalPrice*$CAmount)/100;
				$result=['couponname'=>$data['couponame'],'discountoffer'=>$discount];
			}
			else
			{
				$result=['couponname'=>$data['couponame'],'discountoffer'=>$CAmount];
			} 
			
		return json_encode($result);
		}
		}else{
			 $result=['error_msg'=>'Invaild Coupon Code!!'];
			 return json_encode($result);
		}
		
		}
		 else{
			 $result=['error_msg'=>'Invaild Coupon Code!!'];
			 return json_encode($result);
		 } 
	 }
	}
	else{
		echo "Invaild Users!";
	}

	}

	}
