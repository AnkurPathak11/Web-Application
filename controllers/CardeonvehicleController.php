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
use app\models\Documents;
use app\models\CaravanLatestDocuments;
use app\models\CardoenLatestDocuments;
use app\models\AddressMaster;
use app\models\Streets;
use app\models\CardoenDetailsForPolicyRevision;
use app\models\InsuranceDetails;

class CardeonvehicleController extends Controller
{
	
	
	
	public function actionUpdatevehicledetails(){
		$data=Yii::$app->request->post();
		$cardeonvehicle = CardoenDetails::find()->where(['Id' =>$data['CardoenDetails']['Id'],'ClientId'=>$data['CardoenDetails']['ClientId']])->one();
		//$cardeonvehicle->Gedomicilieerd = $data['CardoenDetails']['Gedomicilieerd'];
		//$cardeonvehicle->Nationaliteit =  $data['CardoenDetails']['Nationaliteit'];

		$cardeonvehicle->Type =  $data['CardoenDetails']['Type'];
		$cardeonvehicle->Merk = $data['CardoenDetails']['Merk'];
		$cardeonvehicle->Brandstof =  $data['CardoenDetails']['Brandstof'];
		$cardeonvehicle->Aantal_plaatsen =  $data['CardoenDetails']['Aantal_plaatsen'];
		$cardeonvehicle->Datum_1ste_ingebruikname =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Datum_1ste_ingebruikname'])));
		$cardeonvehicle->Bonus_malus =  $data['CardoenDetails']['Bonus_malus'];
		$cardeonvehicle->Ingangsdatum_contract =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Ingangsdatum_contract'])));
		$cardeonvehicle->Ingangsdatum_bijvoegsel =date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Ingangsdatum_bijvoegsel'])));
		$cardeonvehicle->Betalingsperiodiciteit =  $data['CardoenDetails']['Betalingsperiodiciteit'];
		
		//$cardeonvehicle->Rijbewijsnummer = $data['CardoenDetails']['Rijbewijsnummer'];
		
        //$cardeonvehicle->Afgelopen = $data['CardoenDetails']['Afgelopen'];
		//$cardeonvehicle->Waarvan_schades= $data['CardoenDetails']['Waarvan_schades'];
        //$cardeonvehicle->Bonus_malus = $data['CardoenDetails']['Bonus_malus'];
		//$cardeonvehicle->Gewenste_startdatum = date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Gewenste_startdatum'])));

		$cardeonvehicle->Verzekerde_waarde = $data['CardoenDetails']['Verzekerde_waarde'];
		$cardeonvehicle->KiloWatt = $data['CardoenDetails']['KiloWatt'];
		$cardeonvehicle->NummerPlaat = $data['CardoenDetails']['NummerPlaat'];
		//$cardeonvehicle->Datum_inverkeersstelling =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Datum_inverkeersstelling'])));
		$cardeonvehicle->Chassisnummer = $data['CardoenDetails']['Chassisnummer'];
		//$cardoenlatestdocuments->NummerPlaat = $data['CardoenDetails']['NummerPlaat'];
		$cardeonvehicle->Insurance_option = $data['CardoenDetails']['Insurance_option'];
		$cardeonvehicle->BA = $data['CardoenDetails']['BA'];
		$cardeonvehicle->Full_Omnium = $data['CardoenDetails']['Full_Omnium'];
		$cardeonvehicle->Kleine_omnium = $data['CardoenDetails']['Kleine_omnium'];
		$cardeonvehicle->Rechtsbijstand = $data['CardoenDetails']['Rechtsbijstand'];
		$cardeonvehicle->Bestuurders_verzekering = $data['CardoenDetails']['Bestuurders_verzekering'];
		if($cardeonvehicle->save(false))
		{
			$cardeonvehicle->update();
		//$cardoenLatestDocuments->update();		
			Yii::$app->session->setFlash('success', 'Vehicle details updated successfully.');
			return $this->redirect(['/site/success']); 
		}

	}
	
	
	public function actionSavepolicyrevisiondetails(){
		$data=Yii::$app->request->post();
		
		$cardeonPolicyRevisionDetails=new CardoenDetailsForPolicyRevision();
		$cardeonPolicyRevisionDetails->Type =  $data['CardoenDetailsForPolicyRevision']['Type'];
		$cardeonPolicyRevisionDetails->ClientId =  $data['CardoenDetailsForPolicyRevision']['ClientId'];
		$cardeonPolicyRevisionDetails->Merk = $data['CardoenDetailsForPolicyRevision']['Merk'];
		$cardeonPolicyRevisionDetails->Brandstof =  $data['CardoenDetailsForPolicyRevision']['Brandstof'];
		$cardeonPolicyRevisionDetails->Aantal_plaatsen =  $data['CardoenDetailsForPolicyRevision']['Aantal_plaatsen'];
		$cardeonPolicyRevisionDetails->Datum_1ste_ingebruikname =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['Datum_1ste_ingebruikname'])));
		$cardeonPolicyRevisionDetails->Bonus_malus =  $data['CardoenDetailsForPolicyRevision']['Bonus_malus'];
		$cardeonPolicyRevisionDetails->Ingangsdatum_contract =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['Ingangsdatum_contract'])));
		$cardeonPolicyRevisionDetails->Ingangsdatum_bijvoegsel =date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['Ingangsdatum_bijvoegsel'])));
		$cardeonPolicyRevisionDetails->Betalingsperiodiciteit =  $data['CardoenDetailsForPolicyRevision']['Betalingsperiodiciteit'];
		$cardeonPolicyRevisionDetails->Verzekerde_waarde = $data['CardoenDetailsForPolicyRevision']['Verzekerde_waarde'];
		$cardeonPolicyRevisionDetails->KiloWatt = $data['CardoenDetailsForPolicyRevision']['KiloWatt'];
		$cardeonPolicyRevisionDetails->NummerPlaat = $data['CardoenDetailsForPolicyRevision']['NummerPlaat'];
		//$cardeonvehicle->Datum_inverkeersstelling =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Datum_inverkeersstelling'])));
		$cardeonPolicyRevisionDetails->Chassisnummer = $data['CardoenDetailsForPolicyRevision']['Chassisnummer'];
		//$cardoenlatestdocuments->NummerPlaat = $data['CardoenDetails']['NummerPlaat'];
		$cardeonPolicyRevisionDetails->Insurance_option = $data['CardoenDetailsForPolicyRevision']['Insurance_option'];
		$cardeonPolicyRevisionDetails->BA = $data['CardoenDetailsForPolicyRevision']['BA'];
		$cardeonPolicyRevisionDetails->Full_Omnium = $data['CardoenDetailsForPolicyRevision']['Full_Omnium'];
		$cardeonPolicyRevisionDetails->Kleine_omnium = $data['CardoenDetailsForPolicyRevision']['Kleine_omnium'];
		$cardeonPolicyRevisionDetails->Rechtsbijstand = $data['CardoenDetailsForPolicyRevision']['Rechtsbijstand'];
		$cardeonPolicyRevisionDetails->Bestuurders_verzekering = $data['CardoenDetailsForPolicyRevision']['Bestuurders_verzekering'];
		$cardeonPolicyRevisionDetails->Datum_Status =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['Datum_Status'])));
		$cardeonPolicyRevisionDetails->Premie_betaald_tot = date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['Premie_betaald_tot'])));
		$cardeonPolicyRevisionDetails->Bijvoegseltype = $data['CardoenDetailsForPolicyRevision']['Bijvoegseltype'];
		$cardeonPolicyRevisionDetails->StatusContract = $data['CardoenDetailsForPolicyRevision']['StatusContract'];
		
		$cardeonPolicyRevisionDetails->ProductId =  $data['CardoenDetailsForPolicyRevision']['ProductId'];
		$cardeonPolicyRevisionDetails->transaction_id =  $data['CardoenDetailsForPolicyRevision']['transaction_id'];
		$cardeonPolicyRevisionDetails->ProductName =  $data['CardoenDetailsForPolicyRevision']['ProductName'];
		$cardeonPolicyRevisionDetails->Category =  $data['CardoenDetailsForPolicyRevision']['Category'];
		$cardeonPolicyRevisionDetails->InvoiceNo =  $data['CardoenDetailsForPolicyRevision']['InvoiceNo'];
		$cardeonPolicyRevisionDetails->CustomerRefNo =  $data['CardoenDetailsForPolicyRevision']['CustomerRefNo'];
		$cardeonPolicyRevisionDetails->policyNo =  $data['CardoenDetailsForPolicyRevision']['policyNo'];
		$cardeonPolicyRevisionDetails->status =  $data['CardoenDetailsForPolicyRevision']['status'];
		
		$cardeonPolicyRevisionDetails->AanvangsdatumWaarborg_1 =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['AanvangsdatumWaarborg_1'])));
		$cardeonPolicyRevisionDetails->AanvangsdatumWaarborg_2 =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['AanvangsdatumWaarborg_2'])));
		$cardeonPolicyRevisionDetails->AanvangsdatumWaarborg_3 =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['AanvangsdatumWaarborg_3'])));
		$cardeonPolicyRevisionDetails->AanvangsdatumWaarborg_4 =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['AanvangsdatumWaarborg_4'])));
		$cardeonPolicyRevisionDetails->AanvangsdatumWaarborg_5 =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetailsForPolicyRevision']['AanvangsdatumWaarborg_5'])));
		
		$cardeonPolicyRevisionDetails->save();
	//	$cardeonPolicyRevisionDetails->update();
		
		Yii::$app->session->setFlash('success', 'Policy revision details save successfully.');
		return $this->redirect(['/site/success']); 
	}
	
	public function actionUpdatecardetails(){
		$data=Yii::$app->request->post();
		$vehicledetails = InsuranceDetails::find()->where(['Id' =>$data['InsuranceDetails']['Id'],'ClientId'=>$data['InsuranceDetails']['ClientId']])->one();
		$clients = Clients::find()->where(['UserId'=>$data['InsuranceDetails']['ClientId']])->one();
		print_r($data);
		$vehicledetails->VehicleType =  $data['InsuranceDetails']['VehicleType'];
		$vehicledetails->VehicleBrand = $data['InsuranceDetails']['VehicleBrand'];
		$vehicledetails->VehicleFuelType =  $data['InsuranceDetails']['VehicleFuelType'];
		$vehicledetails->Seats =  $data['InsuranceDetails']['Seats'];
		$vehicledetails->VehicleBuildYear = $data['InsuranceDetails']['VehicleBuildYear'];
		$vehicledetails->NumberOfAccidents =  $data['InsuranceDetails']['NumberOfAccidents'];
		$vehicledetails->FirstCirculationDate =  date('Y-m-d',strtotime(str_replace('/', '-',$data['InsuranceDetails']['FirstCirculationDate'])));

		// $vehicledetails->BonusMalus =  $data['Clients']['Bonus_malus'];

		$vehicledetails->PolicyStartDate =  date('Y-m-d',strtotime(str_replace('/', '-',$data['InsuranceDetails']['PolicyStartDate'])));
		$vehicledetails->PolicyEffectiveDate =date('Y-m-d',strtotime(str_replace('/', '-',$data['InsuranceDetails']['PolicyEffectiveDate'])));
		$vehicledetails->PolicyPremiumType =  $data['InsuranceDetails']['PolicyPremiumType'];
		$vehicledetails->VehiclePrice = $data['InsuranceDetails']['VehiclePrice'];
		$vehicledetails->VehiclePower = $data['InsuranceDetails']['VehiclePower'];
		$vehicledetails->VehicleLicensePlate = $data['InsuranceDetails']['VehicleLicensePlate'];		
		$vehicledetails->VehicleChassisNumber = $data['InsuranceDetails']['VehicleChassisNumber'];
		$vehicledetails->VehicleBuildYear = $data['InsuranceDetails']['VehicleBuildYear'];
		$vehicledetails->VehicleOdoMeterCar = $data['InsuranceDetails']['VehicleOdoMeterCar'];
		$vehicledetails->InsuranceOption = $data['InsuranceDetails']['InsuranceOption'];
		$vehicledetails->BA = $data['InsuranceDetails']['BA'];
		$vehicledetails->Fullomnium = $data['InsuranceDetails']['Fullomnium'];
		$vehicledetails->Kleineomnium = $data['InsuranceDetails']['Kleineomnium'];
		$vehicledetails->Rechtsbijstand = $data['InsuranceDetails']['Rechtsbijstand'];
		$vehicledetails->Bijstand = $data['InsuranceDetails']['Bijstand'];
		$vehicledetails->Bestuurdersverzekering = $data['InsuranceDetails']['Bestuurdersverzekering'];

		// $clients->Bonus_malus = $data['Clients']['Bonus_malus'];

		if($vehicledetails->save(false) && $clients->save(false))
		{
			$vehicledetails->update();		
			$clients->update();
			Yii::$app->session->setFlash('success', 'Vehicle details updated successfully.');
			return $this->redirect(['/site/success']); 
		}

	}
	
	
}