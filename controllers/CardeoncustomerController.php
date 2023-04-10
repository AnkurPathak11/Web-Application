<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\CardoenDetails;
use app\models\Clients;
use app\models\Products;
use app\models\CardoenRequest;
use app\models\MyProducts;
use app\models\ClientsNotRegistered;
use app\models\Documents;
use app\models\CaravanLatestDocuments;
use app\models\CardoenLatestDocuments;
use app\models\AddressMaster;
use app\models\Streets;
use app\models\InsuranceRequest;
use app\models\InsuranceDetails;

class CardeoncustomerController extends Controller {
	
	public function actionNewcustomerdetails(){
	//$transaction = Yii::$app->db->beginTransaction();
		$clientsdetails = new ClientsNotRegistered();
		$cardeonvehicle = new CardoenDetails();
		$streetArr = array();

		if (Yii::$app->request->post()){
			$cardoenReq = new CardoenRequest();
			$data = Yii::$app->request->post();

			$this->saveCardoenRequest($cardoenReq,$data);
			if ($cardoenReq->save(false)){
				$t_id =$cardoenReq->transaction_id;
				$this->saveClientDetails($t_id,$clientsdetails,$data);
				if ($clientsdetails->UserId != null)
				{
					$this->saveCardoenDetails($clientsdetails,$cardeonvehicle,$data,$t_id);	
					$this->saveMyProductDetails($clientsdetails,$cardeonvehicle);
					Yii::$app->session->setFlash('success', 'Klant succesvol geregistreerd.');
					return $this->render('/site/success.php');
				}
			}

		}else{

			return $this->render('/cardeoncustomer/includecardeondetails.php',['clientsDetails'=>$clientsdetails,'streetArr'=>$streetArr,'cardeonmodel'=>$cardeonvehicle]);
		}
		

	}
	function saveCardoenRequest($cardoenReq,$data){
		$cardoenReq->customerfirstname=$data['Clients']['FirstName'];
		$cardoenReq->customerfamilyname=$data['Clients']['LastName'];
		$cardoenReq->customertel=$data['Clients']['PhoneNo'];
		$cardoenReq->customeremailaddress=$data['Clients']['Email'];
		$cardoenReq->customeraddressstreetname=$data['Clients']['Street'];
		$cardoenReq->customeraddresscity=$data['Clients']['Gemeente'];
		$cardoenReq->customeraddresshouseno=$data['Clients']['Nr'];
		$cardoenReq->customeraddressboxno=$data['Clients']['Bus'];
		$cardoenReq->customertel=$data['Clients']['MobileNo'];
		$cardoenReq->customerdob= date('d/m/Y',strtotime(str_replace('/', '-',$data['Clients']['DateOfBirth'])));
		$cardoenReq->customerdgendercode=$data['Clients']['Gender']; 
		$cardoenReq->customeraddresspostalcode=$data['Clients']['PostalCode'];
		$cardoenReq->customerprofessioncategorycod1 =$data['Clients']['Beroep'];	
		$cardoenReq->customerdrivinglicensenumber =$data['Clients']['Rijksregisternummer'];
		//$cardoenReq->customerdrivinglicenseissuedo =date('d/m/Y',strtotime(str_replace('/', '-',$data['Clients']['Rijbewijs_sinds'])));
		$cardoenReq->customerdrivinglicenseissuedo =$data['Clients']['DrivingLicenseDate'];
		$cardoenReq->customerlanguagecode=$data['Clients']['language'];
		
		$cardoenReq->customervehiclekilowatts =$data['CardoenDetails']['KiloWatt'];
		$cardoenReq->customervehiclefirstdatetraf =date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Ingangsdatum_bijvoegsel'])));
		$cardoenReq->customervehiclechassisnumber =$data['CardoenDetails']['Chassisnummer'];	
		$cardoenReq->customervehiclelicenseplate =$data['CardoenDetails']['NummerPlaat'];	
		$cardoenReq->customervehicleprice =$data['CardoenDetails']['Verzekerde_waarde'];	
		$cardoenReq->customervehicletype =$data['CardoenDetails']['Type'];	
		$cardoenReq->customervehiclemake =$data['CardoenDetails']['Merk'];	
		
		$cardoenReq->requestdate = date('d/m/Y');	
		$cardoenReq->save(false);
	}

	
	
	function saveClientDetails($t_id,$clientsdetails,$data){
		$clientsdetails->FirstName=$data['Clients']['FirstName'];
		$clientsdetails->LastName=$data['Clients']['LastName'];
		$clientsdetails->PhoneNo=$data['Clients']['PhoneNo'];
		$clientsdetails->Email=$data['Clients']['Email'];
		$clientsdetails->Street=$data['Clients']['Street'];
		$clientsdetails->Gemeente=$data['Clients']['Gemeente'];
		$clientsdetails->Nr=$data['Clients']['Nr'];
		$clientsdetails->Bus=$data['Clients']['Bus'];
		$clientsdetails->MobileNo=$data['Clients']['MobileNo'];
		$clientsdetails->PhoneNo=$data['Clients']['PhoneNo'];
		$clientsdetails->DateOfBirth=date('Y-m-d',strtotime(str_replace('/', '-',$data['Clients']['DateOfBirth'])));
		$clientsdetails->Gender=$data['Clients']['Gender']; 
		$clientsdetails->PostalCode=$data['Clients']['PostalCode'];
		$clientsdetails->Beroep =$data['Clients']['Beroep'];	
		$clientsdetails->Rijksregisternummer =$data['Clients']['Rijksregisternummer'];
		//$clientsdetails->Rijbewijs_sinds =date('Y-m-d',strtotime(str_replace('/', '-',$data['Clients']['Rijbewijs_sinds'])));	
		$clientsdetails->Waarvan_schades =$data['Clients']['Waarvan_schades'];
		$clientsdetails->Bonus_malus =$data['Clients']['Bonus_malus'];
		$clientsdetails->Aantal_plaatsen =$data['Clients']['Aantal_plaatsen'];
		$clientsdetails->transaction_id =0;//$data['Clients']['transaction_id'];
		$clientsdetails->language =$data['Clients']['language'];
		$clientsdetails->Licence_Number =$data['Clients']['Licence_Number'];		
		$clientsdetails->DrivingLicenseDate=date('Y-m-d',strtotime(str_replace('/', '-',$data['Clients']['DrivingLicenseDate'])));
		$clientsdetails->save(false);
	}
	
	
	function saveCardoenDetails($clientsdetails,$cardeonvehicle,$data,$t_id){
		$Product = Products::find()->where(['ProductId' =>4])->one();	
		$modelMyProduct =new MyProducts();
		$cardeonvehicle->ProductName = $Product->ProductName;
		$cardeonvehicle->Category=$Product->Category;
		$cardeonvehicle->ProductId = 4;
		$cardeonvehicle->status = 'Added via Admin Panel';			
		$cardeonvehicle->transaction_id = $t_id;
		$cardeonvehicle->ClientId = $clientsdetails->UserId;
		$cardeonvehicle->FirstName = $clientsdetails->FirstName;
		$cardeonvehicle->LastName = $clientsdetails->LastName;
		$cardeonvehicle->PhoneNo = $clientsdetails->PhoneNo;
		$cardeonvehicle->Email = $clientsdetails->Email;
		$cardeonvehicle->StreetName = $clientsdetails->Street;
		$cardeonvehicle->Town = $clientsdetails->Gemeente;
		$cardeonvehicle->Number = $clientsdetails->Nr;
		$cardeonvehicle->Bus = $clientsdetails->Bus;
		$cardeonvehicle->Mobile = $clientsdetails->MobileNo; 
		$cardeonvehicle->Type =  $data['CardoenDetails']['Type'];
		$cardeonvehicle->Merk = $data['CardoenDetails']['Merk'];
		$cardeonvehicle->Brandstof =  $data['CardoenDetails']['Brandstof'];
		$cardeonvehicle->Aantal_plaatsen =  $data['CardoenDetails']['Aantal_plaatsen'];
		$cardeonvehicle->Datum_1ste_ingebruikname =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Datum_1ste_ingebruikname'])));
		$cardeonvehicle->Bonus_malus =  $data['CardoenDetails']['Bonus_malus'];
		$cardeonvehicle->Ingangsdatum_contract =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Ingangsdatum_contract'])));
		$cardeonvehicle->Ingangsdatum_bijvoegsel =date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Ingangsdatum_bijvoegsel'])));
		$cardeonvehicle->Betalingsperiodiciteit =  $data['CardoenDetails']['Betalingsperiodiciteit'];
		$cardeonvehicle->Verzekerde_waarde = $data['CardoenDetails']['Verzekerde_waarde'];
		$cardeonvehicle->KiloWatt = $data['CardoenDetails']['KiloWatt'];
		$cardeonvehicle->NummerPlaat = $data['CardoenDetails']['NummerPlaat'];
		$cardeonvehicle->Chassisnummer = $data['CardoenDetails']['Chassisnummer'];
		$cardeonvehicle->Insurance_option = $data['CardoenDetails']['Insurance_option'];
		$cardeonvehicle->BA = $data['CardoenDetails']['BA'];
		$cardeonvehicle->Full_Omnium = $data['CardoenDetails']['Full_Omnium'];
		$cardeonvehicle->Kleine_omnium = $data['CardoenDetails']['Kleine_omnium'];
		$cardeonvehicle->Rechtsbijstand = $data['CardoenDetails']['Rechtsbijstand'];
		$cardeonvehicle->Bestuurders_verzekering = $data['CardoenDetails']['Bestuurders_verzekering'];
		$cardeonvehicle->save(false);
	}
	
	function saveMyProductDetails($clientsdetails,$cardeonvehicle){	
		$Product = Products::find()->where(['ProductId' =>4])->one();
		$modelMyProduct =new MyProducts();
		$modelMyProduct->ProductName = $Product->ProductName;
		$modelMyProduct->Category=$Product->Category;
		$modelMyProduct->FormDetailsId = $cardeonvehicle->Id;
		$modelMyProduct->ProductId = 4;
		$modelMyProduct->UserId = $clientsdetails->UserId;
		$modelMyProduct->save();
	}
	
	public function actionNewclientdetails(){
	//$transaction = Yii::$app->db->beginTransaction();
		$clientsdetails = new Clients();
		$vehicledetails = new InsuranceDetails();
		$request = new InsuranceRequest();
		$streetArr = array();

		if (Yii::$app->request->post()){
			$insuranceReq = new InsuranceRequest();
			$data = Yii::$app->request->post();

			$this->saveInsuranceRequest($insuranceReq,$data);
			if ($insuranceReq->save(false)){
				$t_id =$insuranceReq->transaction_id;
				$this->saveClientDetails($t_id,$clientsdetails,$data);
				if ($clientsdetails->UserId != null)
				{
					$this->saveInsuranceDetails($clientsdetails,$vehicledetails,$data,$t_id);	
					$this->saveProductDetails($clientsdetails,$vehicledetails);
					Yii::$app->session->setFlash('success', 'Klant succesvol geregistreerd.');
					return $this->render('/site/success.php');
				}
			}

		}else{
			if($_GET['category'] == 'verschueren'){			
				$vehicledetails->CarDealer = 'verschueren';
				$vehicledetails->ProductId = 9;
			}
			else if($_GET['category'] == 'beerens'){
				$vehicledetails->CarDealer = 'beerens';
				$vehicledetails->ProductId = 8;
			}
			else if($_GET['category'] == 'cardoen'){
				$vehicledetails->CarDealer = 'cardoen';
				$vehicledetails->ProductId = 4;
			}		
			else if($_GET['category'] == 'dex'){
				$vehicledetails->CarDealer = 'dex';
				$vehicledetails->ProductId = 11;
			}

			return $this->render('/cardeoncustomer/includeinsurancedetails.php',['clientsDetails'=>$clientsdetails,'streetArr'=>$streetArr,'vehiclemodel'=>$vehicledetails,'productName'=>$vehicledetails->CarDealer,'productId'=>$vehicledetails->ProductId, 'request'=>$request]);
		}

	}
	
	function saveInsuranceRequest($insuranceReq,$data){

		$insuranceReq->PolicyHolderFirstName=$data['Clients']['FirstName'];
		$insuranceReq->PolicyHolderLastName=$data['Clients']['LastName'];
		$insuranceReq->PolicyHolderTel=$data['Clients']['PhoneNo'];
		$insuranceReq->PolicyHolderEmailAddress=$data['Clients']['Email'];
		$insuranceReq->PolicyHolderAddressStreetName=$data['Clients']['Street'];
		$insuranceReq->PolicyHolderAddressCity=$data['Clients']['Gemeente'];
		$insuranceReq->PolicyHolderAddressHouseNo=$data['Clients']['Nr'];
		$insuranceReq->PolicyHolderAddressBoxNo=$data['Clients']['Bus'];		
		$insuranceReq->PolicyHolderBirthDate= date('d/m/Y',strtotime(str_replace('/', '-',$data['Clients']['DateOfBirth'])));
		$insuranceReq->policyHolderGender=$data['Clients']['Gender']; 
		$insuranceReq->PolicyHolderAddressPostalCode=$data['Clients']['PostalCode'];
		$insuranceReq->PolicyHolderProfessionCategoryCode =$data['Clients']['Beroep'];	
		$insuranceReq->PolicyHolderDrivingLicenceNumber =$data['Clients']['Licence_Number'];
		//$insuranceReq->PolicyHolderDrivingLicenceDate =date('d/m/Y',strtotime(str_replace('/', '-',$data['Clients']['Rijbewijs_sinds'])));
		$insuranceReq->PolicyHolderDrivingLicenceDate =$data['Clients']['DrivingLicenseDate'];
		$insuranceReq->PolicyHolderLanguage=$data['Clients']['language'];
		
		$insuranceReq->VehiclePower =$data['InsuranceDetails']['VehiclePower'];
		$insuranceReq->VehicleFirstcirculationDate =date('Y-m-d',strtotime(str_replace('/', '-',$data['InsuranceDetails']['FirstCirculationDate'])));
		$insuranceReq->VehicleChassisNumber =$data['InsuranceDetails']['VehicleChassisNumber'];	
		$insuranceReq->VehicleLicensePlate =$data['InsuranceDetails']['VehicleLicensePlate'];	
		$insuranceReq->VehicleBuildYear = $data['InsuranceDetails']['VehicleBuildYear'];
		$insuranceReq->VehiclePrice =$data['InsuranceDetails']['VehiclePrice'];	
		$insuranceReq->VehicleType =$data['InsuranceDetails']['VehicleType'];	
		$insuranceReq->VehicleBrand =$data['InsuranceDetails']['VehicleBrand'];	
		$insuranceReq->CarDealer =$data['InsuranceDetails']['CarDealer'];
		$insuranceReq->NumberOfAccidents =$data['InsuranceDetails']['NumberOfAccidents'];
		$insuranceReq->NumberOfClaims =$data['InsuranceDetails']['NumberOfClaims'];
		$insuranceReq->VehicleOdoMeterCar =$data['InsuranceDetails']['VehicleOdoMeterCar'];
		$insuranceReq->policyHolderDrivingBandLastFiveYears = $data['InsuranceRequest']['policyHolderDrivingBandLastFiveYears'];		
		$insuranceReq->DocumentRequestDate = date('d/m/Y');	
		$insuranceReq->CreatedDate = date('Y-m-d H:i:s');
		$insuranceReq->save(false);
	}
	
	function saveInsuranceDetails($clientsdetails,$vehicledetails,$data,$t_id){
		$Product = Products::find()->where(['ProductId' =>$data['InsuranceDetails']['ProductId']])->one();	
		$modelMyProduct =new MyProducts();
		$vehicledetails->ProductId = $Product->ProductId;
		$vehicledetails->Status = 'Added via Admin Panel';			
		$vehicledetails->transaction_id = $t_id;
		$vehicledetails->ClientId = $clientsdetails->UserId;		
		$vehicledetails->VehicleType =  $data['InsuranceDetails']['VehicleType'];
		$vehicledetails->VehicleBrand = $data['InsuranceDetails']['VehicleBrand'];
		$vehicledetails->VehicleFuelType =  $data['InsuranceDetails']['VehicleFuelType'];
		$vehicledetails->VehicleBuildYear = $data['InsuranceDetails']['VehicleBuildYear'];
		$vehicledetails->VehicleOdoMeterCar = $data['InsuranceDetails']['VehicleOdoMeterCar'];
		$vehicledetails->Seats =  $data['InsuranceDetails']['Seats'];
		$vehicledetails->NumberOfAccidents =  $data['InsuranceDetails']['NumberOfAccidents'];
		$vehicledetails->NumberOfClaims =  $data['InsuranceDetails']['NumberOfClaims'];
		$vehicledetails->FirstCirculationDate =  date('Y-m-d',strtotime(str_replace('/', '-',$data['InsuranceDetails']['FirstCirculationDate'])));

		// $vehicledetails->BonusMalus =  $data['Clients']['Bonus_malus'];

		$vehicledetails->PolicyStartDate =  date('Y-m-d',strtotime(str_replace('/', '-',$data['InsuranceDetails']['PolicyStartDate'])));
		$vehicledetails->PolicyEffectiveDate =date('Y-m-d',strtotime(str_replace('/', '-',$data['InsuranceDetails']['PolicyEffectiveDate'])));
		$vehicledetails->PolicyPremiumType =  $data['InsuranceDetails']['PolicyPremiumType'];
		$vehicledetails->VehiclePrice = $data['InsuranceDetails']['VehiclePrice'];
		$vehicledetails->VehiclePower = $data['InsuranceDetails']['VehiclePower'];
		$vehicledetails->VehicleLicensePlate = $data['InsuranceDetails']['VehicleLicensePlate'];
		$vehicledetails->VehicleChassisNumber = $data['InsuranceDetails']['VehicleChassisNumber'];

		$vehicledetails->InsuranceOption = $data['InsuranceDetails']['InsuranceOption'];
		$vehicledetails->BA = $data['InsuranceDetails']['BA'];
		$vehicledetails->Fullomnium = $data['InsuranceDetails']['Fullomnium'];
		$vehicledetails->Kleineomnium = $data['InsuranceDetails']['Kleineomnium'];
		$vehicledetails->Rechtsbijstand = $data['InsuranceDetails']['Rechtsbijstand'];
		$vehicledetails->Bijstand = $data['InsuranceDetails']['Bijstand'];
		$vehicledetails->Bestuurdersverzekering = $data['InsuranceDetails']['Bestuurdersverzekering'];
		$vehicledetails->CarDealer = $data['InsuranceDetails']['CarDealer'];
		$vehicledetails->save(false);
	}
	
	function saveProductDetails($clientsdetails,$vehicledetails){	
		$Product = Products::find()->where(['ProductId' =>$vehicledetails->ProductId])->one();
		$modelMyProduct =new MyProducts();
		$modelMyProduct->ProductName = $Product->ProductName;
		$modelMyProduct->Category=$Product->Category;
		$modelMyProduct->FormDetailsId = $vehicledetails->Id;
		$modelMyProduct->ProductId = $Product->ProductId;
		$modelMyProduct->UserId = $clientsdetails->UserId;
		$modelMyProduct->save();
	}
	
	
	
}