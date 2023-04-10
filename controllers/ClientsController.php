<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\CaravanDetails;
use app\models\CardoenDetails;
use app\models\Products;
use app\models\MyProducts;
use app\models\Clients;
use app\models\DriverDetails;
use app\models\Documents;
use app\models\CaravanLatestDocuments;
use app\models\CardoenLatestDocuments;
use app\models\AddressMaster;
use app\models\Streets;
use app\models\CardoenRequest;

class ClientsController extends Controller
{
	
	public function actionUpdatedetails(){
		$data=Yii::$app->request->post();
		//$client = Clients::find()->where(['Email' =>$data['Clients']['Email']])->one();
		//$client = Clients::find()->where(['transaction_id'=>$data['Clients']['transaction_id']])->one();
		$client = Clients::find()->where(['UserId'=>$data['Clients']['UserId']])->one();
		//$cardoenReq = CardoenRequest::find()->where(['transaction_id' =>$data['Clients']['transaction_id']])->one();
		$client->FirstName=$data['Clients']['FirstName'];
		$client->LastName=$data['Clients']['LastName'];
		$client->PhoneNo=$data['Clients']['PhoneNo'];
		$client->Email=$data['Clients']['Email'];
		$client->Street=$data['Clients']['Street'];
		$client->Gemeente=$data['Clients']['Gemeente'];
		$client->Nr=$data['Clients']['Nr'];
		$client->Bus=$data['Clients']['Bus'];
		$client->MobileNo=$data['Clients']['MobileNo'];
		$client->PhoneNo=$data['Clients']['PhoneNo'];
		$client->DateOfBirth=date('Y-m-d',strtotime(str_replace('/', '-',$data['Clients']['DateOfBirth'])));
		$client->Gender=$data['Clients']['Gender']; 
		$client->PostalCode=$data['Clients']['PostalCode'];
		$client->Beroep =$data['Clients']['Beroep'];	
		$client->Rijksregisternummer =$data['Clients']['Rijksregisternummer'];
		//$client->Rijbewijs_sinds =date('Y-m-d',strtotime(str_replace('/', '-',$data['Clients']['Rijbewijs_sinds'])));	
		$client->Waarvan_schades =$data['Clients']['Waarvan_schades'];
		$client->Bonus_malus =$data['Clients']['Bonus_malus'];
		$client->Aantal_plaatsen =$data['Clients']['Aantal_plaatsen'];
		$client->language =$data['Clients']['language'];
		$client->customerdrivingbandlastfiveyear =$data['Clients']['customerdrivingbandlastfiveyear'];		
		$client->transaction_id =0;
		$client->Licence_Number =$data['Clients']['Licence_Number'];		
		$client->DrivingLicenseDate =date('Y-m-d',strtotime(str_replace('/', '-',$data['Clients']['DrivingLicenseDate'])));
		//$cardoenReq->customerlanguagecode =$data['Clients']['language'];
		if($client->save(false) /* && $cardoenReq->save(false) */)
		{
		$client->update();
		//$cardoenReq->update();
		Yii::$app->session->setFlash('success', 'User details update successfully.');
		return $this->redirect(['/site/success']);
		}
		}
				
		public function actionSavedriverdetails(){
		$data = Yii::$app->request->post();
		if (Yii::$app->request->post()){
		$driver =  DriverDetails::find()->where(['UserId' =>$data['DriverDetails']['UserId']])->one();
		if($data['DriverDetails']['UserId'] == "" ){
			$driver = new DriverDetails();
		}
		$driver->FirstName=$data['DriverDetails']['FirstName'];
		$driver->LastName=$data['DriverDetails']['LastName'];
		$driver->PhoneNo=$data['DriverDetails']['PhoneNo'];
		$driver->Street=$data['DriverDetails']['Street'];
		$driver->Gemeente=$data['DriverDetails']['Gemeente'];
		$driver->Nr=$data['DriverDetails']['Nr'];
		$driver->Bus=$data['DriverDetails']['Bus'];
		$driver->MobileNo=$data['DriverDetails']['MobileNo'];
		$driver->DateOfBirth=date('Y-m-d',strtotime(str_replace('/', '-',$data['DriverDetails']['DateOfBirth'])));
		$driver->PostalCode=$data['DriverDetails']['PostalCode'];
		$driver->Beroep =$data['DriverDetails']['Beroep'];	
		$driver->Rijksregisternummer =$data['DriverDetails']['Rijksregisternummer'];
		$driver->Licence_Number =$data['DriverDetails']['Licence_Number'];
		$driver->Rijbewijs_sinds =date('Y-m-d',strtotime(str_replace('/', '-',$data['DriverDetails']['Rijbewijs_sinds'])));
		$driver->Hoofdbestuurder =$data['DriverDetails']['Hoofdbestuurder'];
		$driver->Id = $data['DriverDetails']['Id'];
		$driver->ClientId =$data['DriverDetails']['ClientId'];
		$driver->Email =$data['DriverDetails']['Email'];
		$driver->Waarvan_schades =$data['DriverDetails']['Waarvan_schades'];
		$driver->Bonus_malus =$data['DriverDetails']['Bonus_malus'];
		$driver->Language =$data['DriverDetails']['Language'];
		if(isset($data['DriverDetails']['Aantal_plaatsen'])){
		$driver->Aantal_plaatsen =$data['DriverDetails']['Aantal_plaatsen'];	
		}
		$driver->transaction_id =$data['DriverDetails']['transaction_id'];
		$STATUS= FALSE; 
		if($data['DriverDetails']['UserId'] != "" ){
			//$driver->UserId =$data['DriverDetails']['UserId'];
			$STATUS=$driver->update();
			
			}
		else{
			$STATUS=$driver->save(false);
			}
		 
		if($STATUS){	
			Yii::$app->session->setFlash('success','Driver details added successfully.');
			return $this->redirect(['/site/success']);
		}else{
			return $this->redirect(['/site/error']);
		} 
	}
	
	}

	public function actionGetpolicyholder(){
	if (Yii::$app->request->post()){
		$data =  Yii::$app->request->post();
		$Clientsdetails = Clients::find()->where(['UserId' =>$data['ClientId'] ])->one();
		$result=[
		'firstName'=>$Clientsdetails['FirstName'],
		'lastName'=>$Clientsdetails['LastName'],
		'phoneno'=>$Clientsdetails['PhoneNo'],
		'email'=>$Clientsdetails['Email'],
		'street'=>$Clientsdetails['Street'],
		'gemeente'=>$Clientsdetails['Gemeente'],
		'nr'=>$Clientsdetails['Nr'],
		'bus'=>$Clientsdetails['Bus'],
		'mobileno'=>$Clientsdetails['MobileNo'],
		'dateofbirth'=>$Clientsdetails['DateOfBirth'],
		'gender'=>$Clientsdetails['Gender'],
		'postalCode'=>$Clientsdetails['PostalCode'],
		'beroep'=>$Clientsdetails['Beroep'],
		'rijksregisternummer'=>$Clientsdetails['Rijksregisternummer'],
		'rijbewijs_sinds'=>$Clientsdetails['Rijbewijs_sinds'],
		'waarvan_schades'=>$Clientsdetails['Waarvan_schades'],
		'bonus_malus'=>$Clientsdetails['Bonus_malus'],
		'aantal_plaatsen'=>$Clientsdetails['Aantal_plaatsen'],
		'licence_Number'=>$Clientsdetails['Licence_Number'],
		'drivingLicenseDate'=>$Clientsdetails['DrivingLicenseDate'],
		];
		
		return json_encode($result);
	 }
	
	}

	 public function actionGetcityname(){
		
	  $data =  Yii::$app->request->post();
	  $addressMaster = AddressMaster::find()->where(['PostalCode' =>$data['postalcode']])->one();
	  $streets=Streets::find()->where(['PostalCode' =>$data['postalcode']])->select('StreetName')->orderBy('StreetName ASC')->all();
	  $array = array();
	  foreach($streets as $street){
	   array_push($array, $street->StreetName);
	  }  
	  $json=array('city'=>$addressMaster->Street,
	  'streets'=>$array);
		return json_encode($json);
	}

	
	}
	
