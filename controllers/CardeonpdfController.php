<?php
namespace app\controllers;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\CardoenDetails;
use app\models\Products;
use app\models\MyProducts;
use app\models\Clients;
use app\models\Documents;
use app\models\CaravanLatestDocuments;
use app\models\CardoenLatestDocuments;
use app\models\AddressMaster;
use app\models\Streets;
use app\models\InsuranceDetails;
use app\models\CardoenRequest;
use app\controllers\Config;

use app\controllers\helper\AzureStorage;

//use MicrosoftAzure\Storage\File\FileRestProxy;
//use MicrosoftAzure\Storage\Common\ServiceException;
//define('__ROOT__', dirname(dirname(__FILE__)));
//require_once "../vendor/autoload.php";



class CardeonpdfController extends Controller
{
	public function gettransactionid(){
	$data=Yii::$app->request->post();	
	$cardeonvehicle = CardoenDetails::find()->where(['Id' =>$data['offerid'],'ClientId'=>$data['clientid']])->one();		
	//$cardeonvehicle->transaction_id
	$transaction_id = $cardeonvehicle['transaction_id'];
	return $transaction_id;
	} 
	
	public function actionCardeonpdfcreation(){
	$data =Yii::$app->request->post();

	$cardeonvehicle = CardoenDetails::find()->where(['Id' =>$data['offerid'],'ClientId'=>$data['clientid']])->one();		
	$transaction_id = $cardeonvehicle['transaction_id'];
	//$cardeonrequest = CardoenRequest::find()->where(['transaction_id' =>$transaction_id])->one();
	//$ir_id = $cardeonrequest['ir_id'];
	$Verzekeringen = $cardeonvehicle['Insurance_option'];
	$Ingangsdatum_contract = $cardeonvehicle['Ingangsdatum_contract'];
	$Betalingsperiodiciteit = $cardeonvehicle['Betalingsperiodiciteit'];
	
	//$transactionid = $this->gettransactionid();
	
if (Yii::$app->request->post()){
	$endpoint="";
if($data['doctype']=="pdf1"){
	$endpoint="createPack1";
}
else if($data['doctype']=="pdf2"){
	$endpoint="createPack2Admin";	
}
else if($data['doctype']=="pdf3"){
	$endpoint="createPack3";
}
else if($data['doctype']=="pdf4"){
	if(empty($Verzekeringen) || empty($Ingangsdatum_contract) || empty($Betalingsperiodiciteit) ){
		return "Beleid kan niet worden gegenereerd als Betaling Periodiciteit of Verzekeringen of Ingangsdatum contract zijn niet gedefinieerd";
}
	
		else{
		$endpoint="createPack4";
		}
		
		}
else if($data['doctype']=="pdf5"){
	$endpoint="createPack5";
		}


	$http_method = "POST";
	$url ="http://localhost:8280/innogarantPdfMaker/innogarant_api/CreatePdfs/".$endpoint;	
	//$newdata = array();
	$newdata= array(
	"transActionId"=>$transaction_id,
	//"vendorId"=>"dfd",
	//"documentType"=>$data['doctype'],
	);
	$options = array(
        'http' => array(           
            'header'  => "Content-Type: application/json\r\n",						 
			'method'  => 'POST',
            'content' => json_encode($newdata)
        )
    );
	$context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
	 // print_r($result);
		if($result)
       {
		echo $result;
       }
	   else{
        echo  "108";
       }
	  
	   } 
	 
	}
	
	public function actionCardeonmailcreation(){
	$data =Yii::$app->request->post();
	$cardeonvehicle = CardoenDetails::find()->where(['Id' =>$data['offerid'],'ClientId'=>$data['clientid']])->one();		
	$transaction_id = $cardeonvehicle['transaction_id'];
	//$cardeonrequest = CardoenRequest::find()->where(['transaction_id' =>$transaction_id])->one();
	//$ir_id = $cardeonrequest['ir_id'];
	$endpoint="";
	
	if($data['doctype']=="pdf2"){
		$endpoint="pack2";		
	}
	else if($data['doctype']=="pdf1"){
		$endpoint="pack1";		
	}
	else if($data['doctype']=="pdf4"){
		$endpoint="pack4";		
	}
	else if($data['doctype']=="pdf5"){
		$endpoint="pack5";
	}
	
	$http_method = "POST";
	$url ="http://localhost:8280/innogarantPdfMaker/innogarant_api/sendEmail/".$endpoint;	
	//$newdata = array();
	
	$newdata= array(
	'transActionId'=>$transaction_id,
	'magicLink'=>true,
	);
	
	$options = array(
    'http' => array(
            'header'  => array("Content-Type: application/json",
						 "Authorization: Basic ". base64_encode("auto_ver:ver@2020")),
			'method'  => 'POST',
			'timeout'=>'120',
            'content' => json_encode($newdata)
        )
    );
	$context  = stream_context_create($options);
    $result = file_get_contents($url,false,$context);
	if($result)
     {
	echo $result;
     }
	   else{
        echo  "108";
       } 
	 
	}
	
	
	
	public function actionBriojsonfilecreation(){
		
	$data =Yii::$app->request->post();
	$cardeonvehicle = CardoenDetails::find()->where(['Id' =>$data['offerid'],'ClientId'=>$data['clientid']])->one();		
	$transaction_id = $cardeonvehicle['transaction_id'];
    //$cardeonrequest = CardoenRequest::find()->where(['transaction_id' =>$transaction_id])->one();
	//$ir_id = $cardeonrequest['ir_id'];
	
	 $endpoint="";
	if($data['doctype']=="Original"){
		$endpoint="createBrioTxt";
		
	}
	else if($data['doctype']=="Revised"){
		$endpoint="createRevisedBrioTxt";
		
	}
	
	$http_method = "POST";	
	$url ="http://localhost:8280/innogarantPdfMaker/innogarant_api/CreatePdfs/".$endpoint;
	//$newdata = array();
	$newdata= array(
	'transActionId'=>$transaction_id,
	);
	
	$options = array(
    'http' => array(
            'header'  => "Content-Type: application/json\r\n",
			'method'  => 'POST',
			'timeout'=>'120',
            'content' => json_encode($newdata)
        )
    );
	$context  = stream_context_create($options);
    $result = file_get_contents($url,false,$context);
	if($result)
     {
	echo $result;
     }
	   else{
        echo  "108";
       } 
	 
	}
		
	
	// new api call to download the file as response 
		public function actionDownloadgeneratedfile(){
			// $product = $_GET['product'];
			$docType=$_GET['doctype'];
			$fileName = $docType;
			$productName = $_GET['productname'];
			/*$connectionString = 'DefaultEndpointsProtocol=https;AccountName=innogarantstorage ;AccountKey=814XVuXMEmgQhsAf1yWto1VG6LSfpYlGhA9Rnkgkcy1QMAxWWyOzq4xfKr0vhgIMtDAsi/7RaasI8nUTUpj/Vg==';
			$fileClient = FileRestProxy::createFileService($connectionString);
			$file = $fileClient->getFile("files/".$productName, $fileName);
		    
			$source = stream_get_contents($file->getContentStream());
			 header("Content-type: application/octet-stream");
		    header("Content-disposition: attachment;filename=".$docType);
			
			return $source;*/

			return AzureStorage::azurestorage($docType,$fileName,$productName);
			
		}
		
		
			// new api call to download the file as response 
		public function actionDownloadgeneratedinsurancefile(){
			//$product = $_GET['product'];
			$docType=$_GET['doctype'];
			$fileName = $docType;
			$productName = $_GET['productname'];
			$product = Products::find()->where('DealerCode =:DealerCode', [':DealerCode' => $productName])->one();
			//$cloudStorageDir = $product->CloudStorageDir;
			//$connectionString = 'DefaultEndpointsProtocol=https;AccountName=innogarantstorage ;AccountKey=814XVuXMEmgQhsAf1yWto1VG6LSfpYlGhA9Rnkgkcy1QMAxWWyOzq4xfKr0vhgIMtDAsi/7RaasI8nUTUpj/Vg==';
			//$fileClient = FileRestProxy::createFileService($connectionString);
			//$file = $fileClient->getFile($cloudStorageDir, $fileName);
			//$source = stream_get_contents($file->getContentStream());
			//$file = fopen("../files/".$productName."/".$fileName,"r");		
			//$source = stream_get_contents($file);
			//header("Content-type: application/octet-stream");
		    //header("Content-disposition: attachment;filename=".$docType);
			//fclose($file);
			//return $source;
			return AzureStorage::azurestorage($docType,$fileName,$productName);
			
		}			
	
	public function actionInsurancepdfcreation(){
	$data =Yii::$app->request->post();

	$vehicledata = InsuranceDetails::find()->where(['Id' =>$data['offerid'],'ClientId'=>$data['clientid']])->one();		
	$transaction_id = $vehicledata['transaction_id'];	
	$Verzekeringen = $vehicledata['InsuranceOption'];
	$Ingangsdatum_contract = $vehicledata['PolicyStartDate'];
	$Betalingsperiodiciteit = $vehicledata['PolicyPremiumType'];	

	
	if (Yii::$app->request->post()){
	$endpoint="";
    if($data['doctype']=="pdf2"){
	$endpoint="offer/revision";
	}
	else if($data['doctype']=="pdf4"){
	if(empty($Verzekeringen) || empty($Ingangsdatum_contract) || empty($Betalingsperiodiciteit) ){
		return "Beleid kan niet worden gegenereerd als Betaling Periodiciteit of Verzekeringen of Ingangsdatum contract zijn niet gedefinieerd";
	}
	
	else{
		$endpoint="policy/generate";
		}
		
		}
	else if($data['doctype']=="pdf7"){
	$endpoint="policy/generate";
	$this->updateInsuranceOptions($transaction_id, '7dayfree');
	
     }

    else if($data['doctype']=="pdf8"){
	$endpoint="policy/generate";
	$this->updateInsuranceOptions($transaction_id, 'leasewith14daydiscount');
	
    }		

	$http_method = "POST";
	$url ="http://localhost:8280/CarInsurance/api/car/".$endpoint;
	//$newdata = array();
	$newdata= array(
	"transaction_id"=>$transaction_id,
	);
	$options = array(
        'http' => array(
            /* 'header'  =>array("Content-Type: application/json\r\n",
							  "Authorization: Basic".base64_encode("Ashish:Password")), */
            'header'  => array("Content-Type: application/json",
						 //"Authorization: Basic ". base64_encode("auto_ver:ver@2020")),
						 "Authorization: ".Config::getAuthorization()),
			'method'  => 'POST',
            'content' => json_encode($newdata)
        )
    );
	$context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
	 // print_r($result);
		if($result)
       {
		echo $result;
       }
	   else{
        echo  "108";
       }
	  
	   } 
	 
	}
	
	public function actionInsurancemailcreation(){
	$data =Yii::$app->request->post();
	$cardeonvehicle = InsuranceDetails::find()->where(['Id' =>$data['offerid'],'ClientId'=>$data['clientid']])->one();		
	$transaction_id = $cardeonvehicle['transaction_id'];
	$endpoint="";
	
	if($data['doctype']=="pdf2"){
		$endpoint="offer";
	}
	else if($data['doctype']=="pdf4" || $data['doctype']=="pdf7" || $data['doctype']=="pdf8"){
		$endpoint="policy";
	}
	
	$http_method = "POST";
	$url ="http://localhost:8280/CarInsurance/api/send-email/insurance/".$endpoint;
	//$newdata = array();
	
	$newdata= array(
	'transaction_id'=>$transaction_id,
	'magicLink'=>true,
	);
	
	$options = array(
    'http' => array(
            'header'  => array("Content-Type: application/json",
						 //"Authorization: Basic ". base64_encode("auto_ver:ver@2020")),
						   "Authorization: ".Config::getAuthorization()),
			'method'  => 'POST',
			'timeout'=>'120',
            'content' => json_encode($newdata)
        )
    );
	$context  = stream_context_create($options);
    $result = file_get_contents($url,false,$context);
	if($result)
     {
	echo $result;
     }
	   else{
        echo  "108";
       } 
	 
	}
	
	
	public function actionBrioinsurancejsonfilecreation(){
		
	$data =Yii::$app->request->post();
	$vehicledata = InsuranceDetails::find()->where(['Id' =>$data['offerid'],'ClientId'=>$data['clientid']])->one();		
	$transaction_id = $vehicledata['transaction_id'];
     
	 $endpoint="";
	if($data['doctype']=="Original"){
		$endpoint="createbriotxt";
	}
	/*else if($data['doctype']=="Revised"){
		$endpoint="createRevisedBrioTxt";
	}*/
	
	$http_method = "POST";
	$url ="http://localhost:8280/CarInsurance/api/car/offer/".$endpoint;
	//$newdata = array();
	$newdata= array(
	'transaction_id'=>$transaction_id,
	);
	
	$options = array(
    'http' => array(
            'header'  => array("Content-Type: application/json",
						 //"Authorization: Basic ". base64_encode("auto_ver:ver@2020")),
						   "Authorization: ".Config::getAuthorization()),
			'method'  => 'POST',
			'timeout'=>'120',
            'content' => json_encode($newdata)
        )
    );
	$context  = stream_context_create($options);
    $result = file_get_contents($url,false,$context);
	if($result)
     {
	echo $result;
     }
	   else{
        echo  "108";
       } 
	
	
	}
	
	public function updateInsuranceOptions($transactionId, $option){
		$insurancedetail = InsuranceDetails::find()->where(['transaction_id' =>$transactionId])->one();
		
		$insurancedetail->InsuranceOption =  $option;
		if($option=="7dayfree"){
		$insurancedetail->PolicyStartDate =  date('Y-m-d',time());
		}
		
		
		$insurancedetail->update();
	}	
	
		
	}