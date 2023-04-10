<?php

namespace app\controllers;
use Yii;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\CaravanDetails;
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

class CaravanController extends Controller
{
 
		public function actionQuote(){
		$streetArr = array();
        $allproducts = Products::find()->all();
		$slotvalues = SCMslot::find()->all();
        if(Yii::$app->user->isGuest) {
           $session=Yii::$app->session;
           $session->set('url',$_SERVER['REQUEST_URI']);
           return $this->redirect(['register/login']);
        }

         $model=new CaravanDetails();
         $modelMyProduct =new MyProducts();

        if (Yii::$app->request->post()) {
			$data =  Yii::$app->request->post();
            $model->ClientId = Yii::$app->user->identity->UserId;
             $model->Object =$data['CaravanDetails']['Object'];
             $model->BuiltYear =$data['CaravanDetails']['BuiltYear'];
             $model->Brand =$data['CaravanDetails']['Brand'];
             $model->Type = $data['CaravanDetails']['Type'];
			 $model->NummerPlaat=$data['CaravanDetails']['NummerPlaat'];
             $model->CaravanLocation =$data['CaravanDetails']['CaravanLocation'];
             $model->AwningCancopy =$data['CaravanDetails']['AwningCancopy'];
             $model->Accessories = $data['CaravanDetails']['Accessories'];
             $model->Waardegarantie =$data['CaravanDetails']['Waardegarantie'];
             $model->HailBestDing =$data['CaravanDetails']['HailBestDing'];
             $model->Gender =  $data['CaravanDetails']['Gender'];
             $model->FirstName =  $data['CaravanDetails']['FirstName'];
             $model->LastName =  $data['CaravanDetails']['LastName'];
            
             $model->QuoteNo=	$data['CaravanDetails']['QuoteNo'];

            $model->DateOfBirth =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CaravanDetails']['DateOfBirth'])));
            $model->DateOfFirstUse =  date('Y-m-d',strtotime(str_replace('/', '-',$data['CaravanDetails']['DateOfFirstUse'])));
			$model->QuoteIssueDate = $data['CaravanDetails']['QuoteIssueDate'];
			$model->DateOfcommencement = date('Y-m-d',strtotime(str_replace('/', '-',$data['CaravanDetails']['DateOfcommencement'])));
            $model->Address =  $data['CaravanDetails']['Address'];
            $model->Town =  $data['CaravanDetails']['Town'];
            $model->StreetName =  $data['CaravanDetails']['StreetName'];
            $model->Number =  $data['CaravanDetails']['Number'];
             $model->Bus =  $data['CaravanDetails']['Bus'];
             $model->PhoneNo =  $data['CaravanDetails']['PhoneNo'];
             $model->Mobile =  $data['CaravanDetails']['Mobile'];
             $model->Email =  $data['CaravanDetails']['Email'];
             $model->ProductPrice = $data['CaravanDetails']['ProductPrice'];
			$model->SCMgekeurdslot=$data['CaravanDetails']['SCMgekeurdslot'];
			$model->Couponame=$data['CaravanDetails']['Couponame'];
			$model->DiscountOffer=$data['CaravanDetails']['DiscountOffer'];
			$model->doc1File = UploadedFile::getInstance($model, 'doc1File');
			$model->doc2File = UploadedFile::getInstance($model, 'doc2File');
			$model->doc3File = UploadedFile::getInstance($model, 'doc3File');
			$model->doc4File = UploadedFile::getInstance($model, 'doc4File');
			$Product = Products::find()->where(['ProductId' =>$data['CaravanDetails']['ProductId']])->one();
            $model->ProductId=$data['CaravanDetails']['ProductId'];
			$model->ProductName = $Product->ProductName;
			$model->Category = $Product->Category;
			$model->Subtotal=	$data['CaravanDetails']['Subtotal'];
			$model->Proratavalue=	$data['CaravanDetails']['Proratavalue'];
			$this->initUploadfileDetails($model);
           $this->generateSequenceNo($model);
		   
		   $model->save();
			 		 if ($model->upload()) {
            }
			$this->updateUserDetails($model);
            $modelMyProduct->ProductName = $Product->ProductName;
			$modelMyProduct->Category=$Product->Category;
			$modelMyProduct->FormDetailsId = $model->Id;
            $modelMyProduct->ProductId = $data['CaravanDetails']['ProductId'];
            $modelMyProduct->UserId = Yii::$app->user->identity->UserId;
            $modelMyProduct->save();
            $http_body['type'] = "redirect";
            $http_body['order_id'] =   $model->QuoteNo;
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
			$this->redirect(array("caravan/laststep"));
			}
				$id=Yii::$app->user->id;
				$data = Clients::find()->where('UserId =:UserId', [':UserId' => $id])->one();
		    if (Yii::$app->request->get()){
				$model->HailBestDing = $_GET['hagelbestendig_dak'];
				$model->Waardegarantie = $_GET['waarde_dekking'];
				$nieuwprijs=$_GET['nieuwprijs'];
				$remove€=preg_replace('/- €[0-9][0-9][0-9][0-9][0-9]/',' ', $nieuwprijs);
				$model->CaravanLocation = str_replace(array('€'), ' ',$remove€);
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
				if(isset($data->PostalCode))
				{
				 $model->Address=$data->PostalCode;
				 $streets=Streets::find()->where(['PostalCode' =>$data->PostalCode])->select('StreetName')->orderBy('StreetName ASC')->all();
				 foreach($streets as $street){
				 $streetArr[$street->StreetName] = $street->StreetName; 
				}
				}
				
				
			}
    $getData['nieuwprijs'] = $_GET['nieuwprijs'];
    $getData['waarde_dekking'] = $_GET['waarde_dekking'];
    $getData['hagelbestendig_dak'] = $_GET['hagelbestendig_dak'];
	return $this->render('insueanceform',['model'=>$model,'getData'=>$getData,'allproducts'=>$allproducts,'modelMyProduct'=>$modelMyProduct,'slotvalues'=>$slotvalues,'streetArr'=>$streetArr]);
	
	}
	
	public function actionLaststep(){
	return $this->render('last_step');
	}
	
	public function actionAlgemenevoorwaarden(){
	return $this->render('algemenevoorwaarden');
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


	public function actionSendOfferByMail($model){
	$fileName1=Yii::$app->basePath.'/files/'.$model->QuoteNo.'-Caravan.pdf';
	$fileName2=Yii::$app->basePath.'/files/'.$model->QuoteNo.'-Polis.pdf';
	$fileName3=Yii::$app->basePath.'/files/'.$model->QuoteNo.'-Bericht.pdf';
	$fileName4=Yii::$app->basePath.'/files/'.$model->QuoteNo.'-Mifid.pdf';
	$Email=$model->Email;
	
	$content ="<p>Hallo ".$model->FirstName."<br>".'Allereerst,'."<b>".'bedankt voor je vertrouwen !'."</b>"."<br>".'Je ontvangt deze e-mail met de samenvatting van je premieberekening in een offerte op maat, alsook een voorlopige polis, een vervaldagbericht en een informatiedocument voor het gebruik van je persoonsgegevens.'."<br>"."<b>".'Wat moet je nu doen?'."</b>"."<br>".'Een ondertekend voorstel is noodzakelijk om de verzekering van je caravan aan te vatten vanaf de aanvangsdatum die je hebt ingevuld.'."<br>".'Indien dit momenteel niet mogelijk is, maar de premie direct betaalt, dan ben je gedekt van zodra we je betaling hebben ontvangen. (zie de algemene voorwaarden voor meer details)'."<br>".'De andere documenten kunnen later in jouw portaal toegevoegd worden.'."<br>".'We vragen dit binnen de kortste termijn te doen om je korting te behouden.'."<br>".'Ondertussen regelen wij alles voor jou (polis, eventueel opzeg bij huidige maatschappij,...)'."</p>";
	$content .="<p>".'Met vriendelijke groeten,'."<br>".'Het VerzekerJe team '."</p>";
	$content .="<p>"."<img src='https://verzekerje.be/images/kva_logo.jpg' width='145'>"."</img>"."<a href='https://www.facebook.com/kegelsvanantwerpen/?fref=ts'>"." <img src='https://verzekerje.be/images/fb_icon.png' >"."</img>"."</a>"."<a href='https://www.linkedin.com/company-beta/10795044'>"." <img src='https://verzekerje.be/images/tiwter_icon.png'>"."</img>"."</a>"."</p>"."<br>";
	$content .="<p>".'Kegels & Van Antwerpen NV'."<br>".'IJzerlaan 11 | 2060 Antwerpen'."<br>".'mail: '."<a href='joyce.rom@kegelsvanantwerpen.be'>".'info@verzekerje.be'."</a>".'  |  '.'web'."<a href='http://www.kegelsvanantwerpen.be/'>".'www.verzekerje.be'."</a>"."<br>".'Disclaimer:'."<a href='http://www.kegelsvanantwerpen.be/nl/jurid.php'>".'www.kegelsvanantwerpen.be/nl/jurid.php'."</a>".' - FSMA 109179A – Congresstraat 12-14 | 1000 Brussel'."</p>";
	
		$data=Yii::$app->mailer->compose("@app/mail/layouts/html",["content"=>$content])
				->setTo($Email)
				->setCc('info@verzekerje.be')
				->setFrom('info@verzekerje.be')
				->setReplyTo('info@verzekerje.be')
				->setSubject('Jouw persoonlijk VerzekerJe.be aanbod')
				->attach($fileName1)
				->attach($fileName2)
				->attach($fileName3)
				->attach($fileName4)
				->queue();			
	}

	public function actionPricecalculateguest(){
		if (Yii::$app->request->post()) {
		$data =  Yii::$app->request->post();
		$http_method = "POST";
        $url = "http://localhost:8080/Calculation/insurance/getQuoteValue";
        $ch = curl_init($url);
	 	 $basePath=Yii::$app->basePath;
		if(Yii::$app->user->isGuest) {
			
		$data = array(
			"nieuwprijs"=> $data['nieuwprijs'],
			"waarde_dekking"=> $data['waarde_dekking'],
			"hagelbestendig_dak"=> $data['hagelbestendig_dak'],
			"usertype"=> "guest",		
			);
		}
		else{
			$FirstName=Yii::$app->user->identity->FirstName;
			$data = array(
			"nieuwprijs"=> $data['nieuwprijs'],
			"waarde_dekking"=> $data['waarde_dekking'],
			"hagelbestendig_dak"=> $data['hagelbestendig_dak'],
			"usertype"=>"guest",
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
    $json=json_decode($result,true);

       if($result)
       {
        echo  $json['totalcost'];
		
       }
	   else{
        echo  "108";
       }

        curl_close($ch); 
     }else{
        echo "Invalid request";
     }
	
	}
	
	public function actionPricecalculatepdf(){
        if (Yii::$app->request->post()) {
		$today = date("d/m/Y");
		$data =  Yii::$app->request->post();
		$http_method = "POST";
       $url = "http://localhost:8080/Calculation/insurance/getQuoteValue";
        $ch = curl_init($url);
		$curr_timestamp = date('YmdHis');
        $regUserName=Yii::$app->user->identity->FirstName;
		$basePath=Yii::$app->basePath;
		$data = array(
		"usertype"=>$regUserName,
		"hagelbestendig_dak"=>$data['caravandetails-hailbestding'],
		"nieuwprijs"=>$data['caravandetails-caravanlocation'],
		"waarde_dekking"=>$data['caravandetails-waardegarantie'],
		"inboedel"=>$data['caravandetails-accessories'],
		"waardevoortent"=>$data['caravandetails-awningcancopy'],
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
     }else{
        echo "Invalid request";
     } 
    }

	public function actionPricecalculatepdfforregisteruser(){
		$OfferSequence=new OfferSequence();
        if (Yii::$app->request->post()) {
			$today = date("d/m/Y");
			$data =  Yii::$app->request->post();

		$OfferSequence = OfferSequence::find()->where(['ProductId' =>1])->one();
			
			if($OfferSequence->LastOfferDate ==  $today)
		{
			$offerNo=$OfferSequence->OfferNumber;
			$offerNo=$offerNo+1;
		}else{
			$offerNo=1;
			}
		$prefix=$OfferSequence->Prefix;
			
		$http_method = "POST";
       $url = "http://localhost:8080/Calculation/insurance/generateOfferPdfFile";
        $ch = curl_init($url);
		$curr_timestamp = date('YmdHis');
		
        $Id=Yii::$app->user->id;
        $regUserName=Yii::$app->user->identity->FirstName;
		$basePath=Yii::$app->basePath;
		$data = array(
		"id"=>$Id,
      "firstname"=> $data['caravandetails-firstname'],
      "lastname"=> $data['caravandetails-lastname'],
	  "fulname"=>$data['fulname'],
	  "usertype"=>$regUserName,
      "dob"=> date('Y-m-d',strtotime(str_replace('/', '-',$data['datepickerdob']))),
      "dateoffirstuse"=> date('Y-m-d',strtotime(str_replace('/', '-',$data['dateoffirstuse']))),
      "country"=>"Belgium",
	  "gender"=>$data['gender'],
	  "street"=>$data['caravandetails-streetname'],
	  "bus"=>$data['caravandetails-bus'],
	  "postalcode"=>$data['caravandetails-address'],
	  "phoneno"=>$data['caravandetails-phoneno'],
	  "mobileno"=>$data['caravandetails-mobile'],
	  "email"=>$data['caravandetails-email'],
	  "brand"=>$data['caravandetails-brand'],
	  "type"=>$data['caravandetails-type'],
	  "town"=>$data['caravandetails-town'],
	  "number"=>$data['caravandetails-number'],
	  "numberplate"=>$data['caravandetails-nummerplaat'],
	  "builtyear"=>$data['caravandetails-builtyear'],
	  "hagelbestendig_dak"=>$data['caravandetails-hailbestding'],
	  "nieuwprijs"=>$data['caravandetails-caravanlocation'],
	  "inboedel"=>$data['caravandetails-accessories'],
	  "waarde_dekking"=>$data['caravandetails-waardegarantie'],
	  "waardevoortent"=>$data['caravandetails-awningcancopy'],
	  "scmgekeurdslot"=>$data['caravandetails-scmgekeurdslot'],
	"couponame"=>$data['caravandetails-couponame'],
	"discountoffer"=>$data['caravandetails-discountoffer'],
	 "basepath"=>$basePath,
	 "numberofoffer"=>$offerNo,
	 "prefix"=>$prefix,
	 "aanvangsdatum"=> date('Y-m-d',strtotime(str_replace('/', '-',$data['dateofcommencement']))),
	 "productname"=>"Caravan"	  
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
	
	

	public function updateUserDetails($model){
	$client = Clients::find()->where('UserId =:UserId', [':UserId' => $model->ClientId])->one();//model()->findByPk($model->ClientId);
	$client->Street=$model->StreetName;
	$client->Gemeente=$model->Town;
	$client->Nr=$model->Number;
	$client->Bus=$model->Bus; 
	$client->MobileNo= $model->Mobile;
	$client->PhoneNo=$model->PhoneNo;
	$client->PostalCode= $model->Address;
	$client->DateOfBirth=$model->DateOfBirth;
	$client->save(false);
	

}
	
	public function actionGenerateOthersPdfFile($model){ 
		if (Yii::$app->request->post()) {
			$data =  Yii::$app->request->post();
		$http_method = "POST";
       $url = "http://localhost:8080/Calculation/insurance/generateOthersPdfFile";
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
	  "brand"=>$model->Brand,
	  "type"=>$model->Type,
	  "town"=>$model->Town,
	  "number"=>$model->Number,
	  "numberplate"=>$model->NummerPlaat,
	  "builtyear"=>$model->BuiltYear,
	  "hagelbestendig_dak"=>$model->HailBestDing,
	  "nieuwprijs"=>$model->CaravanLocation,
	  "inboedel"=>$model->Accessories,
	  "waarde_dekking"=>$model->Waardegarantie,
	  "waardevoortent"=>$model->AwningCancopy,
	  "scmgekeurdslot"=>$model->SCMgekeurdslot,
	   "quotenumber"=>$model->QuoteNo,
	  "issuedate"=>$model->QuoteIssueDate,
	  "basepath"=>$basePath,
	  "aanvangsdatum"=>$model->DateOfcommencement,
	  "productname"=>"Caravan",
	  "subtotal"=>$model->Subtotal,
	"proratavalue"=>$model->Proratavalue,
	"invoiceno"=>$model->InvoiceNo,
	 "customerrefno"=>$model->CustomerRefNo,
	 "paymentrefno"=>$model->PaymentRefNo
	
	
	  
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
	
	public function generateSequenceNo($model){
	$customerRefNo=Yii::$app->user->identity->CustomerRefNo;
	 $sequenceGenrator=new SequenceGenrator();
	$sequenceGenrator = SequenceGenrator::find()->where(['SequenceId' =>1])->one();
	$number=intval($sequenceGenrator->NextNumber);
	
	$today = date('Y');
	
	
	$yrLastThreeDigit=substr($today, -3);
	
	$seqNumber=$yrLastThreeDigit.$number; 
	
	$model->InvoiceNo=$seqNumber;
	 $seqNumberModOf97=intval($seqNumber) % 97;
	
	$paymentRefNo=$seqNumber.$seqNumberModOf97; 
	$model->PaymentRefNo=$paymentRefNo;
	$model->CustomerRefNo=$customerRefNo;
	
	
	$sequenceGenrator->NextNumber=$number+1;
		$sequenceGenrator->Number=$number;
		$sequenceGenrator->save();
	
}
	
	public function initUploadfileDetails($model){
	$latestDocuments = CaravanLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$model->ClientId] )->andWhere('ProductId=:ProductId',[':ProductId'=>$model->ProductId])
				->andWhere('VehicleNo=:VehicleNo',[':VehicleNo'=>$model->NummerPlaat])->one();
	if(!isset($latestDocuments))
	{
	$latestDocuments=new CaravanLatestDocuments();
	$latestDocuments->VehicleNo=$model->NummerPlaat;
	$latestDocuments->ProductId=$model->ProductId;
	$latestDocuments->ClientId=$model->ClientId;
	$latestDocuments->QuoteIssueDate=$model->QuoteIssueDate;
	$latestDocuments->QuoteNo=$model->QuoteNo;
	}	
	
	$filePrefix='uploads/'.$model->QuoteNo.'_';
	if(isset($model->doc1File->baseName))
    {
    $model->DocumentUpload1=$filePrefix.$model->doc1File->baseName . '.' . $model->doc1File->extension;
	$this->saveDocumentsInfo($model,'Ondertekend voorstel',$model->DocumentUpload1);
	$latestDocuments->DocFile1=$model->DocumentUpload1;
    }  
	if(isset($model->doc2File->baseName))
    {
    $model->DocumentUpload2=$filePrefix.$model->doc2File->baseName . '.' . $model->doc2File->extension;
	$this->saveDocumentsInfo($model,'S.C.M. Slot attest',$model->DocumentUpload2); 
	$latestDocuments->DocFile2=$model->DocumentUpload2;
    }  
	if(isset($model->doc3File->baseName))
    {
    $model->DocumentUpload3=$filePrefix.$model->doc3File->baseName . '.' . $model->doc3File->extension;
	$this->saveDocumentsInfo($model,'Hagelbestendig dak',$model->DocumentUpload3); 
	$latestDocuments->DocFile3=$model->DocumentUpload3;
    }  
	if(isset($model->doc4File->baseName))
    {
    $model->DocumentUpload4=$filePrefix.$model->doc4File->baseName . '.' . $model->doc4File->extension;
	$this->saveDocumentsInfo($model,'Aankoopfactuur Caravan',$model->DocumentUpload4); 
	$latestDocuments->DocFile4=$model->DocumentUpload4;
    } 
	$latestDocuments->save();
	} 
	
	
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
	
	
}
