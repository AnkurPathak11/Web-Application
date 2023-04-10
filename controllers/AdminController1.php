<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Coupon;
use app\models\Clients;
use app\models\Forgotpass;
use app\models\Changepass;
use app\models\UserDetails;
use app\models\Products;
use app\models\MyProducts;
use app\models\CaravanDetails;
use app\models\CaravanLatestDocuments;
use app\models\Documents;
use app\models\DeleteClientsdetails;

class AdminController extends Controller
{
  
	public function actionDashboard(){	
	if(Yii::$app->user->getIsGuest()){
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
	
	$clientsdetails=Clients::find()->all();
	return $this->render('dashboard',['clientsdetails' =>$clientsdetails]);
	}
	

	public function actionCardeondashboard(){
	if(Yii::$app->user->getIsGuest()){
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
	$this->partialrender('cardeondashboard');
	}	
	$clientsdetails=Clients::find()->all();
	return $this->render('cardeondashboard',['clientsdetails' =>$clientsdetails]);
	}
	
	public function actionGetuserid(){
	$data =  Yii::$app->request->post();
	$clients = Clients::find()->where(['Email' =>$data['email']])->one();
	$json = array('userid'=>$clients->UserId,'name'=>$clients->FirstName);   
	return json_encode($json);
	}

	public function actionDeleteuserid(){
	$Delclientsdetails = new DeleteClientsdetails();
	$Delclientsdetails->deleteclient();
	return;
	}
	
}
