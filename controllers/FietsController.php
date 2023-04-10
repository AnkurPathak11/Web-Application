<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\FietsDetails;
use app\models\Products;
use app\models\OfferSequence;
use app\models\SequenceGenrator;
use app\models\MyProducts;
use app\models\SCMslot;
use app\models\AddressMaster;
use app\models\Streets;
use app\models\Clients;
use app\models\Documents;
use app\models\CaravanLatestDocuments;

class FietsController extends Controller
{
	
	public function actionFietsquote() {	
		$streetArr = array();
        $allproducts = Products::find()->all();
		$slotvalues = SCMslot::find()->all();
		 if(Yii::$app->user->isGuest) {
           $session=Yii::$app->session;
           $session->set('url',$_SERVER['REQUEST_URI']);
           return $this->redirect(['register/login']);
        }
         $model = new FietsDetails();
         $modelMyProduct = new MyProducts();
		 
			if (Yii::$app->request->post()) {
			$data =  Yii::$app->request->post();
            $model->ClientId = Yii::$app->user->identity->UserId;
            $model->Fiestype =$data['FietsDetails']['Fiestype'];
            $model->Aankoopdatum = date('Y-m-d',strtotime(str_replace('/', '-',$data['FietsDetails']['Aankoopdatum'])));
            $model->Merk_van_de_fiets = $data['FietsDetails']['Merk_van_de_fiets'];
			$model->Factuurbedrag = $data['FietsDetails']['Factuurbedrag'];
            $model->Indien_gekend = $data['FietsDetails']['Indien_gekend']; 
			$model->Gender =  $data['FietsDetails']['Gender'];
            $model->FirstName =  $data['FietsDetails']['FirstName'];
            $model->LastName =  $data['FietsDetails']['LastName'];
            $model->QuoteNo=	$data['FietsDetails']['QuoteNo'];
		
			//$model->VechileDOB =  date('Y-m-d',strtotime(str_replace('/', '-',$data['FietsDetails']['VechileDOB'])));
			$model->DateOfBirth =  date('Y-m-d',strtotime(str_replace('/', '-',$data['FietsDetails']['DateOfBirth'])));
            $model->Address = $data['FietsDetails']['Address'];
            $model->Town = $data['FietsDetails']['Town'];
            $model->StreetName = $data['FietsDetails']['StreetName'];
            $model->Number = $data['FietsDetails']['Number'];
            $model->Bus = $data['FietsDetails']['Bus'];
            $model->PhoneNo = $data['FietsDetails']['PhoneNo'];
            $model->Mobile = $data['FietsDetails']['Mobile'];
            $model->Email = $data['FietsDetails']['Email'];
            $model->ProductPrice = $data['FietsDetails']['ProductPrice'];
			//$model->Totale_verzekerde_waarde = $data['FietsDetails']['Totale_verzekerde_waarde'];
			$model->Couponame = $data['FietsDetails']['Couponame'];
			$model->DiscountOffer = $data['FietsDetails']['DiscountOffer'];
			$model->doc1File = UploadedFile::getInstance($model, 'doc1File');
			$model->doc2File = UploadedFile::getInstance($model, 'doc2File');
			$model->doc3File = UploadedFile::getInstance($model, 'doc3File');
			$model->doc4File = UploadedFile::getInstance($model, 'doc4File');$Product = Products::find()->where(['ProductId' =>$data['FietsDetails']['ProductId']])->one();
            $model->ProductId=$data['FietsDetails']['ProductId'];
			$model->ProductName = $Product->ProductName;
			$model->Category = $Product->Category;
			$model->Subtotal= $data['FietsDetails']['Subtotal'];
			$model->Proratavalue= $data['FietsDetails']['Proratavalue'];
			$this->initUploadfileDetails($model);
			$model->save();
			 		 if ($model->upload()) {
            }
            $modelMyProduct->ProductName = $Product->ProductName;
			$modelMyProduct->Category= $Product->Category;
			$modelMyProduct->FormDetailsId = $model->Id;
            $modelMyProduct->ProductId = $data['FietsDetails']['ProductId'];
            $modelMyProduct->UserId = Yii::$app->user->identity->UserId;
            $modelMyProduct->save();
            $http_body['type'] = "redirect";
            $http_body['order_id'] = $model->QuoteNo;
            $http_body['currency'] = "EUR";
            $http_body['amount'] = $model->ProductPrice*100;
            $http_body['description'] = "demo";
            $http_body['payment_options']['cancel_url'] = Yii::$app->request->baseUrl.'/payment/paymentCancel';
            $http_body['payment_options']['redirect_url'] = Yii::$app->request->baseUrl.'/payment/paymentSuccess';
            $http_body['customer']['first_name'] = $model->FirstName;
            $http_body['customer']['last_name'] = $model->LastName;
            $http_body['customer']['address1'] = $model->Address;
            $http_body['customer']['email'] = $model->Email;
            $http_body['customer']['phone'] = $model->PhoneNo;
            $http_body['customer']['city'] = $model->Town;
			echo $http_body['order_id'];
			$this->actionGenerateOthersPdfFile($model);
			$this->actionSendOfferByMail($model);
			$this->redirect(array("fiets/laststep"));
	       }		 
		$id=Yii::$app->user->id;
		$data = Clients::find()->where('UserId =:UserId', [':UserId' => $id])->one();
		 if (Yii::$app->request->get()){
				$model->Fiestype = $_GET['allfiets'];
				$nieuwprijs = $_GET['nieuwprijs'];
				$model->Factuurbedrag=$nieuwprijs;
				$model->FirstName=$data->FirstName;
				$model->LastName=$data->LastName;
				$model->PhoneNo=$data->PhoneNo;
				$model->Email=$data->Email;
				if (isset($data->Street)) 
				$model->StreetName=$data->Street;
				if(isset($data->Gemeente))
				$model->Town=$data->Gemeente;
				 if(isset($data->Nr))
				$model->Number=$data->Nr;
				if(isset($data->Bus))
				$model->Bus=$data->Bus;
				if(isset($data->MobileNo))			
				$model->Mobile=$data->MobileNo;
				if(isset($data->PhoneNo))
				$model->PhoneNo=$data->PhoneNo;
				if(isset($data->DateOfBirth))
				$model->DateOfBirth=$data->DateOfBirth;
				if(isset($data->Gender))
				$model->Gender=$data->Gender; 
				if(isset($data->PostalCode)){
				 $model->Address=$data->PostalCode;
				 $streets=Streets::find()->where(['PostalCode' =>$data->PostalCode])->select('StreetName')->orderBy('StreetName ASC')->all();
				 foreach($streets as $street){
				 $streetArr[$street->StreetName] = $street->StreetName; 	 
				 }
				}
		 }
	
	$getData['allfiets'] = $_GET['allfiets'];
	$getData['nieuwprijs']=$_GET['nieuwprijs'];
	return $this->render('bikeinsueanceform',['model'=>$model,'getData'=>$getData,'allproducts'=>$allproducts,'modelMyProduct'=>$modelMyProduct,'slotvalues'=>$slotvalues,'streetArr'=>$streetArr]);

	}
	
	public function actionFietspricecalculateguest(){
	if (Yii::$app->request->post()) {
		$data =  Yii::$app->request->post();
		$http_method = "POST";
        $url = "http://localhost:8080/Calculation/insurance/getFietsQuoteValue";
        $ch = curl_init($url);
	 	$basePath=Yii::$app->basePath;
		if(Yii::$app->user->isGuest){	
		$data = array(
			"nieuwprijs"=> $data['nieuwprijs'],
			"fiets"=> $data['fiets'],
			"usertype"=> "guest",		
			);
		}
		else{
			$FirstName=Yii::$app->user->identity->FirstName;
			$data = array(
			"nieuwprijs"=> $data['nieuwprijs'],
			"fiets"=> $data['fiets'],
			"usertype"=> "guest",	
			);
			
		}; 
	 	$options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
        );                                                                   
		
	$context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    //$json=json_decode($result,true);

       if($result)
       {
        echo  $result;
		
       }
	   else{
        echo  "108";
       }

        curl_close($ch); 
     }else{
        echo "Invalid request";
     } 	
	}


	public function actionFietspricecalculatepdf(){
	if (Yii::$app->request->post()) {
	$data =  Yii::$app->request->post();
		$http_method = "POST";
        $url = "http://localhost:8080/Calculation/insurance/getFietsQuoteValue";	
		$ch = curl_init($url);
	 	$regUserName=Yii::$app->user->identity->FirstName;
		$basePath=Yii::$app->basePath;
		$data = array(
		"usertype"=>$regUserName,
		"nieuwprijs"=> 1000/* $data['FietsDetails']['Factuurbedrag'] */,
		"fiets"=> $data['fietsdetails-fiestype']
		);
		$options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );
	$context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
  


       if($result)
       {
		echo $result;

       }
	   else{
        echo  "108";
       }

        curl_close($ch);
     }
	 else{
        echo "Invalid request";
     } 
}	

	public function actionFietspricecalculatepdfforregisteruser(){
	$OfferSequence=new OfferSequence();	
		if (Yii::$app->request->post()) {
			$today = date("d/m/Y");
			$data =  Yii::$app->request->post();

		$OfferSequence = OfferSequence::find()->where(['ProductId' =>3])->one();
			
			if($OfferSequence->LastOfferDate ==  $today)
		{
			$offerNo=$OfferSequence->OfferNumber;
			$offerNo=$offerNo+1;
		}else{
			$offerNo=1;
			}
		$prefix=$OfferSequence->Prefix;
			
		$http_method = "POST";
       $url = "http://localhost:8080/Calculation/insurance/generateFietsOfferPdfFile";
        $ch = curl_init($url);
		$curr_timestamp = date('YmdHis');
        $Id=Yii::$app->user->id;
        $regUserName=Yii::$app->user->identity->FirstName;
		$basePath=Yii::$app->basePath;
		$data = array(
		"id"=>$Id,
      "firstname"=> $data['fietsdetails-firstname'],
      "lastname"=> $data['fietsdetails-lastname'],
	  "fulname"=>$data['fulname'],
	  "usertype"=>$regUserName,
      "dob"=> date('Y-m-d',strtotime(str_replace('/', '-',$data['datepickerdob']))),
      "country"=>"Belgium",
	  "gender"=>$data['gender'],
	  "street"=>$data['fietsdetails-streetname'],
	  "bus"=>$data['fietsdetails-bus'],
	  "postalcode"=>$data['fietsdetails-address'],
	  "phoneno"=>$data['fietsdetails-phoneno'],
	  "mobileno"=>$data['fietsdetails-mobile'],
	  "email"=>$data['fietsdetails-email'],
	  "town"=>$data['fietsdetails-town'],
	  "number"=>$data['fietsdetails-number'],
	  "nieuwprijs"=>$data['nieuwprijs'],
	  
	  "fiets"=>$data['fietsdetails-fiestype'],
	  "merkvandefiets"=>$data['fietsdetails-merk_van_de_fiets'],
	 "aankoopdatum" => date('Y-m-d',strtotime(str_replace('/', '-',$data['aankoopdatum_date']))),
	"factuurbedrag"=>$data['fietsdetails-factuurbedrag'],
	"serienummerhangslot"=>$data['fietsdetails-indien_gekend'],
	"couponame"=>$data['caravandetails-couponame'],
	"discountoffer"=>$data['caravandetails-discountoffer'],
	 "basepath"=>$basePath,
	 "numberofoffer"=>$offerNo,
	 "prefix"=>$prefix,
	 "aanvangsdatum"=> date('Y-m-d',strtotime(str_replace('/', '-',$data['dateofcommencement']))),
	 "productname"=>"Fiets"	  
);
	$options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );

		$context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
   

       if($result)
       {
		 $OfferSequence->LastOfferDate=$today;
		$OfferSequence->OfferNumber=$offerNo;
		$OfferSequence->save();
		
		echo $result;

       }
	   else{
        echo  "108";
       }

        curl_close($ch);
     }else{
        echo "Invalid request";
     } 
} 


/* 	public function actionGenerateFietsOthersPdfFile($model){ 
		if (Yii::$app->request->post()) {
			$data =  Yii::$app->request->post();
		$http_method = "POST";
       $url = "http://localhost:8080/Calculation/insurance/generateFietsOthersPdfFile";
        $ch = curl_init($url);
		$curr_timestamp = date('YmdHis');
        $Id=Yii::$app->user->id;
        $regUserName=Yii::$app->user->identity->FirstName;
		$basePath=Yii::$app->basePath;
		$data = array(
		"id"=>$Id,
      "firstname"=> $model->FirstName,
      "lastname"=> $model->LastName,
	  "dob"=>$model->DateOfBirth,
      "dateoffirstuse"=>$model->DateOfFirstUse,
      "country"=>"Belgium",
	  "gender"=>$model->Gender,
	  "street"=>$model->StreetName,
	  "bus"=>$model->Bus,
	  "postalcode"=>$model->Address,
	  "phoneno"=>$model->PhoneNo,
	  "mobileno"=>$model->Mobile,
	  "email"=>$model->Email,
	  //"brand"=>$model->Brand,
	  "fiets"=>$model->Fiestype,
	  "town"=>$model->Town,
	  "number"=>$model->Number,
	  "fiets"=>$model->Fiestype,
	  "merkvandefiets"=> $model->Merk_van_de_fiets,
	  "aankoopdatum"=> $model->Aankoopdatum,
	  "factuurbedrag"=>$model->Factuurbedrag,
		"serienummerhangslot"=>$model->Indien_gekend,
	  "quotenumber"=>$model->QuoteNo,
	  "issuedate"=>$model->QuoteIssueDate,
	  "basepath"=>$basePath,
	  "productname"=>"Fiets",
	
);
$options = array(
        'http' => array(
            'header'  => "Content-Type: application/json\r\n",
            'method'  => 'POST',
            'content' => json_encode($data)
        )
    );

		$context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
       if($result)
       {
		   $latestDocuments = CaravanLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$model->ClientId] )->andWhere('ProductId=:ProductId',[':ProductId'=>$model->ProductId])
				->andWhere('VehicleNo=:VehicleNo',[':VehicleNo'=>$model->NummerPlaat])->one();
				$json=json_decode($result,true);
				
		  $latestDocuments->DocFile6=$json['mifidfilename'];
		  $latestDocuments->update();

       }
	   else{
        echo  "108";
       }

        curl_close($ch);
     }else{
        echo "Invalid request";
     }   
	}

 */





	 public function saveDocumentsInfo($model,$doctype,$docfilepath){
		$uploadDate=date("Y-m-d");
		$documents=new Documents();
		$documents->ClientId=$model->ClientId;
		$documents->ProductId=$model->ProductId;
		$documents->VehicleNo=$model->NummerPlaat;
		$documents->UploadDate=$uploadDate;
		$documents->DocType=$doctype;
		$documents->DocFilePath=$docfilepath;
		$documents->save();	
		} 

		 public function actionLaststep(){
		return $this->render('last_step');
		} 
	
	
	 public function actionAlgemenevoorwaarden(){
	return $this->render('algemenevoorwaarden');
	} 	

}

	