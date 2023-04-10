<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\CardoenDetails;
use app\models\Products;
use app\models\OfferSequence;
use app\models\SequenceGenrator;
use app\models\MyProducts;
use app\models\SCMslot;
use app\models\AddressMaster;
use app\models\Streets;
use app\models\Clients;
use app\models\Documents;
use app\models\CardoenLatestDocuments;

class CardeonController extends Controller{
	
	public function actionAutoquote() {
		$streetArr = array();
        $allproducts = Products::find()->all();
		
		
        if(Yii::$app->user->isGuest) {
           $session=Yii::$app->session;
           $session->set('url',$_SERVER['REQUEST_URI']);
           return $this->redirect(['register/login']);
        }

         $cardeonmodel= new CardoenDetails();
         $modelMyProduct =new MyProducts();
			$clientsDetails =new Clients();
			$latestDocuments =new CardoenLatestDocuments();
	
		if (Yii::$app->request->post()) {
			$data =  Yii::$app->request->post();
            $cardeonmodel->ClientId = Yii::$app->user->identity->UserId;
            $cardeonmodel->Gedomicilieerd =$data['CardoenDetails']['Gedomicilieerd'];
			$cardeonmodel->Nationaliteit =  $data['CardoenDetails']['Nationaliteit'];
			/* $cardeonmodel->Rijksregisternummer =  $data['CardoenDetails']['Rijksregisternummer']; */
			$cardeonmodel->Rijbewijsnummer = $data['CardoenDetails']['Rijbewijsnummer'];
			/* $cardeonmodel->Rijbewijs_sinds = date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Rijbewijs_sinds']))); */
            $cardeonmodel->Afgelopen = $data['CardoenDetails']['Afgelopen'];
			$cardeonmodel->Waarvan_schades= $data['CardoenDetails']['Waarvan_schades'];
            $cardeonmodel->Bonus_malus = $data['CardoenDetails']['Bonus_malus'];
			$cardeonmodel->Gewenste_startdatum = date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Gewenste_startdatum'])));
            $cardeonmodel->Merk = $data['CardoenDetails']['Merk'];
			$cardeonmodel->Type = $data['CardoenDetails']['Type'];
            $cardeonmodel->Verzekerde_waarde = $data['CardoenDetails']['Verzekerde_waarde'];
            $cardeonmodel->KiloWatt = $data['CardoenDetails']['KiloWatt'];
            $cardeonmodel->NummerPlaat = $data['CardoenDetails']['NummerPlaat'];
			$cardeonmodel->Datum_inverkeersstelling = date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['Datum_inverkeersstelling'])));
			$cardeonmodel->Chassisnummer = $data['CardoenDetails']['Chassisnummer'];
			
			$clientsDetails->Gender = $data['CardoenDetails']['Gender'];
            $clientsDetails->FirstName =  $data['CardoenDetails']['FirstName'];
            $clientsDetails->LastName =  $data['CardoenDetails']['LastName'];
            $clientsDetails->QuoteNo=	$data['CardoenDetails']['QuoteNo'];
			$clientsDetails->DateOfBirth =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CardoenDetails']['DateOfBirth'])));
			$cardeonmodel->QuoteIssueDate = $data['CardoenDetails']['QuoteIssueDate'];
            $clientsDetails->Address =  $data['CardoenDetails']['Address'];
            $clientsDetails->Town =  $data['CardoenDetails']['Town'];
            $clientsDetails->StreetName =  $data['CardoenDetails']['StreetName'];
            $clientsDetails->Number =  $data['CardoenDetails']['Number'];
            $clientsDetails->Bus =  $data['CardoenDetails']['Bus'];
            $clientsDetails->PhoneNo =  $data['CardoenDetails']['PhoneNo'];
            $clientsDetails->Mobile =  $data['CardoenDetails']['Mobile'];
            $clientsDetails->Email =  $data['CardoenDetails']['Email'];
            $clientsDetails->ProductPrice = $data['CardoenDetails']['ProductPrice'];
			 
			$cardeonmodel->Couponame= $data['CardoenDetails']['Couponame'];
			$cardeonmodel->DiscountOffer= $data['CardoenDetails']['DiscountOffer'];
			$latestDocuments->doc1File = UploadedFile::getInstance($latestDocuments, 'doc1File');
			$latestDocuments->doc2File = UploadedFile::getInstance($latestDocuments, 'doc2File');
			$latestDocuments->doc3File = UploadedFile::getInstance($latestDocuments, 'doc3File');
			$latestDocuments->doc4File = UploadedFile::getInstance($latestDocuments, 'doc4File');$Product = Products::find()->where(['ProductId' =>$data['CardoenDetails']['ProductId']])->one();
            
			$cardeonmodel->ProductId=$data['CardoenDetails']['ProductId'];
			$cardeonmodel->ProductName = $Product->ProductName;
			$cardeonmodel->Category = $Product->Category;
			$cardeonmodel->Subtotal=	$data['CardoenDetails']['Subtotal'];
			$cardeonmodel->Proratavalue= $data['CardoenDetails']['Proratavalue'];
			$this->initUploadcardeonfileDetails($cardeonmodel);
			
		   $cardeonmodel->save();
			 		 if ($cardeonmodel->upload()) {
            }
			

            $modelMyProduct->ProductName = $Product->ProductName;
			$modelMyProduct->Category=$Product->Category;
			$modelMyProduct->FormDetailsId = $cardeonmodel->Id;
            $modelMyProduct->ProductId = $data['CardoenDetails']['ProductId'];
            $modelMyProduct->UserId = Yii::$app->user->identity->UserId;
            $modelMyProduct->save();
            
			$this->redirect(array("cardeon/laststep"));
			
	       }
				$id=Yii::$app->user->id;
				$data = Clients::find()->where('UserId =:UserId', [':UserId' => $id])->one();
		     if (Yii::$app->request->get()){
				$clientsDetails->FirstName=$data->FirstName;
				$clientsDetails->LastName=$data->LastName;
				$clientsDetails->PhoneNo=$data->PhoneNo;
				$clientsDetails->Email=$data->Email;
				if (isset($data->Street)) 
				$clientsDetails->Street=$data->Street;
				if(isset($data->Gemeente))
				$clientsDetails->Gemeente=$data->Gemeente;
				 if(isset($data->Nr))
				$clientsDetails->Nr=$data->Nr;
				if(isset($data->Bus))
				$clientsDetails->Bus=$data->Bus;
				if(isset($data->MobileNo))			
				$clientsDetails->MobileNo=$data->MobileNo;
				if(isset($data->PhoneNo))
				$clientsDetails->PhoneNo=$data->PhoneNo;
				if(isset($data->DateOfBirth))
				$clientsDetails->DateOfBirth=$data->DateOfBirth;
				if(isset($data->Gender))
				$clientsDetails->Gender=$data->Gender; 
				if(isset($data->PostalCode))
				{
				 $clientsDetails->PostalCode=$data->PostalCode;
				 $streets=Streets::find()->where(['PostalCode' =>$data->PostalCode])->select('StreetName')->orderBy('StreetName ASC')->all();
				 foreach($streets as $street){
				 $streetArr[$street->StreetName] = $street->StreetName;
				 
				}
				}
				
	}
	return $this->render('cardeoninsueanceform',['clientsDetails'=>$clientsDetails,'latestDocuments'=>$latestDocuments,'cardeonmodel'=>$cardeonmodel,'allproducts'=>$allproducts,'modelMyProduct'=>$modelMyProduct,'streetArr'=>$streetArr /*,'vehicledata'=>$vehicledata */]);
	}
	
	public function actionLaststep(){
	return $this->render('last_step');
	}
	
	public function initUploadcardeonfileDetails($cardeonmodel){
	$latestDocuments = CardoenLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$cardeonmodel->ClientId] )->andWhere('ProductId=:ProductId',[':ProductId'=>$cardeonmodel->ProductId])
				->andWhere('VehicleNo=:VehicleNo',[':VehicleNo'=>$cardeonmodel->NummerPlaat])->one();
	if(!isset($latestDocuments))
	{
	$latestDocuments=new CardoenLatestDocuments();
	$latestDocuments->VehicleNo=$cardeonmodel->NummerPlaat;
	$latestDocuments->ProductId=$cardeonmodel->ProductId;
	$latestDocuments->ClientId=$cardeonmodel->ClientId;
	$latestDocuments->QuoteIssueDate=$cardeonmodel->QuoteIssueDate;
	$latestDocuments->QuoteNo=$cardeonmodel->QuoteNo;
	}	
	$filePrefix='uploads/'.$cardeonmodel->QuoteNo.'_';
	if(isset($cardeonmodel->doc1File->baseName))
    {
    $cardeonmodel->DocumentUpload1=$filePrefix.$cardeonmodel->doc1File->baseName . '.' . $cardeonmodel->doc1File->extension;
	$this->saveDocumentsInfo($cardeonmodel,'Ondertekend voorstel',$cardeonmodel->DocumentUpload1);
	$latestDocuments->DocFile1=$cardeonmodel->DocumentUpload1;
    }  
	if(isset($cardeonmodel->doc2File->baseName))
    {
    $cardeonmodel->DocumentUpload2=$filePrefix.$cardeonmodel->doc2File->baseName . '.' . $cardeonmodel->doc2File->extension;
	$this->saveDocumentsInfo($cardeonmodel,'schade-attest',$cardeonmodel->DocumentUpload2); 
	$latestDocuments->DocFile2=$cardeonmodel->DocumentUpload2;
    }  
	if(isset($cardeonmodel->doc3File->baseName))
    {
    $cardeonmodel->DocumentUpload3=$filePrefix.$cardeonmodel->doc3File->baseName . '.' . $cardeonmodel->doc3File->extension;
	$this->saveDocumentsInfo($cardeonmodel,'Rijbewijs',$cardeonmodel->DocumentUpload3); 
	$latestDocuments->DocFile3=$cardeonmodel->DocumentUpload3;
    }  
	if(isset($cardeonmodel->doc4File->baseName))
    {
    $cardeonmodel->DocumentUpload4=$filePrefix.$cardeonmodel->doc4File->baseName . '.' . $cardeonmodel->doc4File->extension;
	$this->saveDocumentsInfo($cardeonmodel,'Aanvraag bankdomiciliëring',$cardeonmodel->DocumentUpload4); 
	$latestDocuments->DocFile4=$cardeonmodel->DocumentUpload4;
    } 
	$latestDocuments->save();
	} 
		
	public function actionAlgemenevoorwaarden(){
	return $this->render('algemenevoorwaarden');
	}	
	
	public function updateUserDetails($cardeonmodel){
	$client = Clients::find()->where('UserId =:UserId', [':UserId' => $cardeonmodel->ClientId])->one();//model()->findByPk($model->ClientId);
	$client->Street=$cardeonmodel->StreetName;
	$client->Gemeente=$cardeonmodel->Town;
	$client->Nr=$cardeonmodel->Number;
	$client->Bus=$cardeonmodel->Bus; 
	$client->MobileNo= $cardeonmodel->Mobile;
	$client->PhoneNo=$cardeonmodel->PhoneNo;
	$client->PostalCode= $cardeonmodel->Address;
	$client->DateOfBirth=$cardeonmodel->DateOfBirth;
	$client->save(false);
	}

	public function saveDocumentsInfo($cardeonmodel,$doctype,$docfilepath){
		$uploadDate=date("Y-m-d");
		$documents=new Documents();
		$documents->ClientId=$cardeonmodel->ClientId;
		$documents->ProductId=$cardeonmodel->ProductId;
		$documents->VehicleNo=$cardeonmodel->NummerPlaat;
		$documents->UploadDate=$uploadDate;
		$documents->DocType=$doctype;
		$documents->DocFilePath=$docfilepath;
		$documents->save();	
		}
	
	}
