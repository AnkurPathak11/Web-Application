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
use app\models\CardoenDetailsForPolicyRevision;
use app\models\CardoenRequest;
use app\models\InsuranceRequest;
use app\models\InsuranceDetails;
use app\models\CardoenOfferAcceptanceDetails;

class ProfileController extends Controller
{
	public function actionOverview(){
		
		if(Yii::$app->user->getIsGuest()) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}
		
		if($this->isUserAdmin()&& isset($_GET['cid']))
		{	 

			$cid=$_GET['cid'];
			
		}
		else
		{
			$cid=Yii::$app->user->id;
		}
		$data = Clients::find()
		->where('UserId =:UserId', [':UserId' => $cid])
		->one();



		$myCategory=MyProducts::getMyProductCategories($cid);
		$allproduct = Products::find()->all();

		$offertype=$_GET['offertype'];
		$products = Products::find()->where('DealerCode=:DealerCode',[':DealerCode'=>$offertype])->one();
		$productId = $products['ProductId'];
		return $this->render('/profile/overview.php', [
			'data' => $data,
			'mycategory'=>$myCategory,
			'allproduct' => $allproduct,
			'offertype' => $offertype,
			'productId'=>$productId,

		]);
	}
	
	public function actionCaravanview(){
		if(Yii::$app->user->isGuest) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}
		if($this->isUserAdmin()&& isset($_GET['cid']))
		{	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}

		$data = Clients::find()->where('UserId =:UserId', [':UserId' => $cid])->one();

		if (isset($_GET['Category'])) {
			$category=$_GET['Category'];
		}
		
		$myCategory=MyProducts::getMyProductCategories($cid);
		
		$allproduct = Products::find()->all();


		$myOffers = CaravanDetails::find()
		->where('Category=:Category',[':Category'=>$category])->andWhere('ClientId=:ClientId',[':ClientId'=>$cid] )->orderBy('Id DESC')
		->all();	
		$latestDocuments = CaravanLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$cid] )->
		andWhere('ProductId=:ProductId',[':ProductId'=>$myOffers[0]['ProductId']])->
		andWhere('VehicleNo=:VehicleNo',[':VehicleNo'=>$myOffers[0]['NummerPlaat']])->one();	
		
		return $this->render('/profile/caravanoverview.php', [
			'data' => $data,
			'mycategory'=>$myCategory,
			'allproduct' => $allproduct,
			'myoffers'=>$myOffers,
			'latestDocuments'=>$latestDocuments,
			'category'=>$category,
			'cid'=>$cid
		]);

		
	}	
	
	public function actionCardoenview(){
		if(Yii::$app->user->isGuest) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}
		if($this->isUserAdmin()&& isset($_GET['cid']))
		{	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}

		$data = Clients::find()->where('UserId =:UserId', [':UserId' => $cid])->one();
		$offertype = $_GET['offertype'];

		if (isset($_GET['Category'])) {
			$category=$_GET['Category'];
		}
		
		$myCategory=MyProducts::getMyProductCategories($cid);

		$allproduct = Products::find()->all();
		$myOffers = CardoenDetails::find()
		->where('Category=:Category',[':Category'=>$category])->andWhere('ClientId=:ClientId',[':ClientId'=>$cid] )->orderBy('Id DESC')
		->all();	

		$latestDocuments= CardoenLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$myOffers[0]['ClientId']] )->andWhere('ProductId=:ProductId',[':ProductId'=>$myOffers[0]['ProductId']])->
		andWhere('offerId=:OfferId',[':OfferId'=>$myOffers[0]['Id']])->one(); 


		return $this->render('/profile/cardeonoverview.php', [
			'data' => $data,
			'mycategory'=>$myCategory,
			'allproduct' => $allproduct,
			'myoffers'=>$myOffers,
			'latestDocuments'=>$latestDocuments, 
			'category'=>$category,
			'cid'=>$cid,
			'offertype'=>$offertype
		]);
	}

	public function actionInsuranceview(){
		if(Yii::$app->user->isGuest) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}
		if($this->isUserAdmin()&& isset($_GET['cid']))
		{	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}

		$data = Clients::find()->where('UserId =:UserId', [':UserId' => $cid])->one();
		$offertype = $_GET['offertype'];

		if (isset($_GET['Category'])) {
			$category=$_GET['Category'];
		}
		
		$myCategory=MyProducts::getMyProductCategories($cid);
		
		$product = Products::find()->where('Category =:Category', [':Category' => $category])->one();
		$productId = $product['ProductId'];                            
		$docpath = $product->DocPath;
		$pos = strpos($docpath, "files");
		$folderpath = substr($docpath, $pos-1);
		$len = strlen($docpath);
		$allproduct = Products::find()->all();
		$myOffers = InsuranceDetails::find()
		->where('ProductId=:ProductId',[':ProductId'=>$product->ProductId])->andWhere('ClientId=:ClientId',[':ClientId'=>$cid] )->orderBy('Id DESC')
		->all();	

		$latestDocuments= CardoenLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$myOffers[0]['ClientId']] )->andWhere('ProductId=:ProductId',[':ProductId'=>$myOffers[0]['ProductId']])->
		andWhere('offerId=:OfferId',[':OfferId'=>$myOffers[0]['Id']])->one(); 


		return $this->render('/profile/insuranceoverview.php', [
			'data' => $data,
			'mycategory'=>$myCategory,
			'allproduct' => $allproduct,
			'myoffers'=>$myOffers,
			'latestDocuments'=>$latestDocuments, 
			'category'=>$category,
			'cid'=>$cid,
			'docpath'=>$docpath,
			'len'=>$len,
			'folderpath'=>$folderpath,
			'offertype'=>$offertype,
			'productId'=>$productId,
		]);
	}	   
	
	public function actionUploadfile(){
		if (Yii::$app->request->post()) {
			$data =  Yii::$app->request->post();

			if($this->isUserAdmin()&& isset($data['cid']))
			{	 				
				$clientId=$data['cid'];

			}else{
				$clientId = Yii::$app->user->identity->UserId;
			}	 


			$client = Clients::find()->where('UserId =:UserId', [':UserId' => $clientId])->one();

			$category=$data['category'];
			$productId =$data['CardoenLatestDocuments']['ProductId'];
			$vehicleNo =$data['CardoenLatestDocuments']['VehicleNo'];
			$model=CardoenLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$clientId] )->andWhere('ProductId=:ProductId',[':ProductId'=>$productId])->
			andWhere('VehicleNo=:VehicleNo',[':VehicleNo'=>$vehicleNo])->one();

			$model->doc1File = UploadedFile::getInstance($model, 'doc1File');
			$model->doc2File = UploadedFile::getInstance($model, 'doc2File');
			$model->doc3File = UploadedFile::getInstance($model, 'doc3File');
			$model->doc4File = UploadedFile::getInstance($model, 'doc4File');
			$model->doc5File = UploadedFile::getInstance($model, 'doc5File');
			$this->initUploadcardeonfileDetails($model);
			
			$myCategory=MyProducts::getMyProductCategories($clientId);
			$allproduct = Products::find()->all();


			$myOffers = CardoenDetails::find()
			->where('Category=:Category',[':Category'=>$category])->andWhere('ClientId=:ClientId',[':ClientId'=>$clientId] )->orderBy('Id DESC')
			->all();
			
			return $this->render('/profile/cardeonoverview.php', [
				'data' => $client,
				'mycategory'=>$myCategory,
				'allproduct' => $allproduct,
				'myoffers'=>$myOffers,
				'latestDocuments'=>$model,
				'category'=>$category,
				'cid'=>$clientId
			]);
			
		}
	}
	
	
	public function initUploadfileDetails($model){		
		$filePrefix='uploads/'.$model->QuoteNo.'_';
		if(isset($model->doc1File->baseName))
		{
			$model->DocFile1=$filePrefix.$model->doc1File->baseName . '.' . $model->doc1File->extension;
			$model->doc1File->saveAs($model->DocFile1,false);    
			$this->saveDocumentsInfo($model,'Ondertekend voorstel',$model->DocFile1); 
		}  
		if(isset($model->doc2File->baseName))
		{

			$model->DocFile2=$filePrefix.$model->doc2File->baseName . '.' . $model->doc2File->extension;
			$model->doc2File->saveAs($model->DocFile2,false);   
			$this->saveDocumentsInfo($model,'S.C.M. Slot attest',$model->DocFile2); 

		}  
		if(isset($model->doc3File->baseName))
		{
			$model->DocFile3=$filePrefix.$model->doc3File->baseName . '.' . $model->doc3File->extension;
			$model->doc3File->saveAs($model->DocFile3,false);
			$this->saveDocumentsInfo($model,'Hagelbestendig dak',$model->DocFile3); 
		}  
		if(isset($model->doc4File->baseName))
		{
			$model->DocFile4=$filePrefix.$model->doc4File->baseName . '.' . $model->doc4File->extension;
			$model->doc4File->saveAs($model->DocFile4,false);
			$this->saveDocumentsInfo($model,'Aankoopfactuur Caravan',$model->DocFile4); 
		} 
		if(isset($model->doc5File->baseName))
		{
			$model->DocFile5=$filePrefix.$model->doc5File->baseName . '.' . $model->doc5File->extension;
			$model->doc5File->saveAs($model->DocFile5,false);
			$this->saveDocumentsInfo($model,'Schade-aangifte',$model->DocFile5);   
		} 	
		$model->save();
	} 

	public function initUploadcardeonfileDetails($model){		
		$filePrefix='uploads/'.$model->QuoteNo.'_';
		if(isset($model->doc1File->baseName))
		{
			$model->DocFile1=$filePrefix.$model->doc1File->baseName . '.' . $model->doc1File->extension;
			$model->doc1File->saveAs($model->DocFile1,false);    
			$this->saveDocumentsInfo($model,'Ondertekend voorstel',$model->DocFile1); 
		}  
		if(isset($model->doc2File->baseName))
		{

			$model->DocFile2=$filePrefix.$model->doc2File->baseName . '.' . $model->doc2File->extension;
			$model->doc2File->saveAs($model->DocFile2,false);   
			$this->saveDocumentsInfo($model,'schade-attest',$model->DocFile2); 

		}  
		if(isset($model->doc3File->baseName))
		{
			$model->DocFile3=$filePrefix.$model->doc3File->baseName . '.' . $model->doc3File->extension;
			$model->doc3File->saveAs($model->DocFile3,false);
			$this->saveDocumentsInfo($model,'Rijbewijs',$model->DocFile3); 
		}  
		if(isset($model->doc4File->baseName))
		{
			$model->DocFile4=$filePrefix.$model->doc4File->baseName . '.' . $model->doc4File->extension;
			$model->doc4File->saveAs($model->DocFile4,false);
			$this->saveDocumentsInfo($model,'Aanvraag bankdomiciliëring',$model->DocFile4); 
		}
		if(isset($model->doc5File->baseName))
		{
			$model->DocFile5=$filePrefix.$model->doc5File->baseName . '.' . $model->doc5File->extension;
			$model->doc5File->saveAs($model->DocFile5,false);
			$this->saveDocumentsInfo($model,'SEPA mandaat signed',$model->DocFile5); 
		} 	
		$model->save();
	} 

	public function saveDocumentsInfo($model,$doctype,$docfilepath){
		$uploadDate=date("Y-m-d");
		$documents=new Documents();
		$documents->ClientId=$model->ClientId;
		$documents->ProductId=$model->ProductId;
		$documents->VehicleNo=$model->VehicleNo;
		$documents->UploadDate=$uploadDate;
		$documents->DocType=$doctype;
		$documents->DocFilePath=$docfilepath;
		$documents->save();	
	}
	
	public function isUserAdmin(){
		$getRolesByUser=Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
		if(is_array($getRolesByUser))
			$role=array_keys($getRolesByUser)[0];
		if(strcmp($role,'admin')==0)
		{	 				
			return true;
			
		}else{
			return false;
		}
	}
	public function actionProfiledetails(){
		$streetArr = array();
		$request = null;
		if(Yii::$app->user->isGuest) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}

		$offerType = $_GET['offertype'];
		$products = Products::find()->where('DealerCode=:DealerCode',[':DealerCode'=>$offerType])->one();
		$productId = $products['ProductId'];
		if($this->isUserAdmin() && isset($_GET['cid']))
		{	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}
		$data = Clients::find()->where('UserId =:UserId', [':UserId' => $cid])->one();
		$streets = Streets::find()->where(['PostalCode' =>$data->PostalCode])->select('StreetName')->orderBy('StreetName ASC')->all();
		foreach($streets as $street){
			$streetArr[$street->StreetName] = $street->StreetName; 
		}			 
		return $this->render('/profile/profiledetails.php', ['clientsDetails' =>$data,'streetArr'=>$streetArr,'cid'=>$cid,'request'=>$request,'offerType'=>$offerType,'productId'=>$productId]);
		
	} 
	
	public function actionCardeondetails(){
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}	
		$vehicledata = CardoenDetails::find()->where('ClientId=:ClientId',[':ClientId'=>$cid])->andWhere('Id=:Id',[':Id'=>$_GET['offerId']])->one();
		$acceptancedetail = CardoenOfferAcceptanceDetails::find()->where('transactionId =:transactionId', [':transactionId' => $vehicledata->transaction_id])->orderBy(['id' =>SORT_DESC])->all();
		$count_val=count($acceptancedetail);	

		if(empty($vehicledata->BA) || $vehicledata->BA == null)
		{
			$vehicledata->BA = "No";
		}
		if(empty($vehicledata->Full_Omnium) || $vehicledata->Full_Omnium == null)
		{
			$vehicledata->Full_Omnium = "No";
		}

		if(empty($vehicledata->Kleine_omnium)|| $vehicledata->Kleine_omnium == null)
		{
			$vehicledata->Kleine_omnium = "No";
		}
		if(empty($vehicledata->Rechtsbijstand) || $vehicledata->Rechtsbijstand == null)
		{
			$vehicledata->Rechtsbijstand = "No";
		}
		if(empty($vehicledata->Bestuurders_verzekering) || $vehicledata->Bestuurders_verzekering == null)
		{
			$vehicledata->Bestuurders_verzekering = "No";
		}


		return $this->render('/profile/cardeonvehicledetails.php', ['cardeonmodel' =>$vehicledata, 'acceptancedetail' =>$acceptancedetail, 'count_val' =>$count_val]);
	} 
	
	
	// work for revised policy pdf generation
	public function actionGeneraterevisedpolicy(){
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}	
		$vehicledata = CardoenDetails::find()->where('ClientId=:ClientId',[':ClientId'=>$cid])->andWhere('Id=:Id',[':Id'=>$_GET['offerId']])->one();
		$cardeonPolicyRevision = new CardoenDetailsForPolicyRevision();
		$cardeonPolicyRevision->setAttributes($vehicledata->attributes);
		if(empty($vehicledata->BA) || $vehicledata->BA == null)
		{
			$vehicledata->BA = "No";
		}
		if(empty($vehicledata->Full_Omnium) || $vehicledata->Full_Omnium == null)
		{
			$vehicledata->Full_Omnium = "No";
		}

		if(empty($vehicledata->Kleine_omnium)|| $vehicledata->Full_Omnium == null)
		{
			$vehicledata->Kleine_omnium = "No";
		}
		if(empty($vehicledata->Rechtsbijstand) || $vehicledata->Rechtsbijstand == null)
		{
			$vehicledata->Rechtsbijstand = "No";
		}
		if(empty($vehicledata->Bestuurders_verzekering) || $vehicledata->Bestuurders_verzekering == null)
		{
			$vehicledata->Bestuurders_verzekering = "No";
		}


		return $this->render('../cardeon/cardeonpolicyrevision.php', ['cardeonmodel' =>$cardeonPolicyRevision]);
	}
	
	
	
	
	
	
	public function actionPdfandemailgeneration(){
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}
		$vehicledata = CardoenDetails::find()->where('ClientId=:ClientId',[':ClientId'=>$cid])->andWhere('Id=:Id',[':Id'=>$_GET['offerId']])->one();
//	return $this->render('/profile/emailpdfgenerate.php', ['model' => $vehicledata]);

	// cardoen request details query 
		$cardoenReq = CardoenRequest::find()->where(['transaction_id' =>$vehicledata['transaction_id']])->one();



		$data = Clients::find()->where('UserId =:UserId', [':UserId' => $cid])->one();

		//if (isset($_GET['Category'])) {
	//	$category=$_GET['Category'];
	//	}
		$category='CardoenInsurance';
		
		$myCategory=MyProducts::getMyProductCategories($cid);

		$allproduct = Products::find()->all();
		$myOffers = CardoenDetails::find()
		->where('Category=:Category',[':Category'=>$category])->andWhere('ClientId=:ClientId',[':ClientId'=>$cid] )->andWhere('Id=:Id',[':Id'=>$_GET['offerId']])->one();
		
		//->orderBy('Id DESC')
		//->all();	
		
//		$latestDocuments= CardoenLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$myOffers[0]['ClientId']] )->andWhere('ProductId=:ProductId',[':ProductId'=>$myOffers[0]['ProductId']])->
//		andWhere('offerId=:OfferId',[':OfferId'=>$myOffers[0]['Id']])->one(); 


		$latestDocuments= CardoenLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$myOffers['ClientId']] )->andWhere('ProductId=:ProductId',[':ProductId'=>$myOffers['ProductId']])->
		andWhere('offerId=:OfferId',[':OfferId'=>$myOffers['Id']])->one(); 

		return $this->render('/profile/emailpdfgenerate.php', [
			'model' => $vehicledata,
			'cardoenrequest' =>$cardoenReq,
			'data' => $data,
			'mycategory'=>$myCategory,
			'allproduct' => $allproduct,
			'myoffers'=>$myOffers,
			'latestDocuments'=>$latestDocuments, 
			'category'=>$category,
			'cid'=>$cid
		]);
	}
	
	public function actionDriverdetails(){
		$streetArr = array();
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}
		$productId = $_GET['productid'];
		$products = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();
		$productName = $products['DealerCode'];
		$postdata=Yii::$app->request->post();

		$driver = DriverDetails::find()->where('Id =:Id',[':Id' => $_GET['offerId']])->
		andWhere('ClientId=:ClientId',[':ClientId'=>$_GET['cid']])->one();

		if(isset($driver->UserId)==false){
			$driver = new DriverDetails();
			$driver->Id = $_GET['offerId'];
			$driver->ClientId = $_GET['cid'];
		}


	//$data = Clients::find()->where('UserId =:UserId', [':UserId' => $cid])->one();
		$streets = Streets::find()->where(['PostalCode' =>$driver->PostalCode])->select('StreetName')->orderBy('StreetName ASC')->all();
		foreach($streets as $street){
			$streetArr[$street->StreetName] = $street->StreetName; 
		}	

		return $this->render('/profile/driverdetails.php',['driverDetails' => $driver,'streetArr'=>$streetArr,'productName'=>$productName,'productId'=>$productId]);
	}
	
	public function actionBriojsonfilecreation(){
		
		$data =Yii::$app->request->post();
		$cardeonvehicle = CardoenDetails::find()->where(['Id' =>$data['offerid'],'ClientId'=>$data['clientid']])->one();		
		$transaction_id = $cardeonvehicle['transaction_id'];
		$http_method = "POST";
		$url ="http://localhost:8280/innogarantPdfMaker/innogarant_api/CreatePdfs/createBrioTxt";
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
	
	public function actionVehicledetails(){

		$request = null;
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}
		$productId = $_GET['productid'];
		$products = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();
		$productName = $products['DealerCode'];
		$vehicledata = InsuranceDetails::find()->where('ClientId=:ClientId',[':ClientId'=>$cid])->andWhere('Id=:Id',[':Id'=>$_GET['offerId']])->one();
		$clientdata = Clients::find()->where('UserId=:UserId',[':UserId'=>$cid])->one();
		if(empty($vehicledata->BA) || $vehicledata->BA == null)
		{
			$vehicledata->BA = "No";
		}
		if(empty($vehicledata->Fullomnium) || $vehicledata->Fullomnium == null)
		{
			$vehicledata->Fullomnium = "No";

		}

		if(empty($vehicledata->Kleineomnium)|| $vehicledata->Kleineomnium == null)
		{
			$vehicledata->Kleineomnium = "No";
		}
		if(empty($vehicledata->Rechtsbijstand) || $vehicledata->Rechtsbijstand == null)
		{
			$vehicledata->Rechtsbijstand = "No";
		}
		if(empty($vehicledata->Bijstand) || $vehicledata->Bijstand == null)
		{
			$vehicledata->Bijstand = "No";
		}
		if(empty($vehicledata->Bestuurdersverzekering) || $vehicledata->Bestuurdersverzekering == null)
		{
			$vehicledata->Bestuurdersverzekering = "No";
		}


		return $this->render('/profile/editvehicledetails.php', ['vehiclemodel' =>$vehicledata, 'clientsDetails' =>$clientdata,'request'=>$request,'productId'=>$productId,'productName'=>$productName]);
	} 
	
	// work for revised policy pdf generation
	public function actionGenerateInsurancerevisedpolicy(){
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}	
		$vehicledata = InsuranceDetails::find()->where('ClientId=:ClientId',[':ClientId'=>$cid])->andWhere('Id=:Id',[':Id'=>$_GET['offerId']])->one();
		$insurancePolicyRevision = new InsuranceDetails();
		$insurancePolicyRevision->setAttributes($vehicledata->attributes);
		if(empty($vehicledata->BA) || $vehicledata->BA == null)
		{
			$vehicledata->BA = "No";
		}
		if(empty($vehicledata->Fullomnium) || $vehicledata->Fullomnium == null)
		{
			$vehicledata->Fullomnium = "No";
		}

		if(empty($vehicledata->Kleineomnium)|| $vehicledata->Kleineomnium == null)
		{
			$vehicledata->Kleineomnium = "No";
		}
		if(empty($vehicledata->Rechtsbijstand) || $vehicledata->Rechtsbijstand == null)
		{
			$vehicledata->Rechtsbijstand = "No";
		}
		if(empty($vehicledata->Bijstand) || $vehicledata->Bijstand == null)
		{
			$vehicledata->Bijstand = "No";
		}
		if(empty($vehicledata->Bestuurdersverzekering) || $vehicledata->Bestuurdersverzekering == null)
		{
			$vehicledata->Bestuurdersverzekering = "No";
		}


		return $this->render('../cardeon/insurancepolicyrevision.php', ['vehiclemodel' =>$insurancePolicyRevision]);
	}
	
	public function actionGenerateinsurancepdfandemail(){
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}
		$vehicledata = InsuranceDetails::find()->where('ClientId=:ClientId',[':ClientId'=>$cid])->andWhere('Id=:Id',[':Id'=>$_GET['offerId']])->one();
//	return $this->render('/profile/emailpdfgenerate.php', ['model' => $vehicledata]);

	// cardoen request details query 
		$insuranceReq = InsuranceRequest::find()->where(['transaction_id' =>$vehicledata['transaction_id']])->one();

		
		$data = Clients::find()->where('UserId =:UserId', [':UserId' => $cid])->one();
		$product = Products::find()->where('ProductId =:ProductId', [':ProductId' => $vehicledata->ProductId])->one();
		$category= $product->Category;
		$productId = $product['ProductId']; 
		$myCategory=MyProducts::getMyProductCategories($cid);
		$productName = $product->DealerCode;
		//$productName = strtolower($productName);
		$allproduct = Products::find()->all();
		$myOffers = InsuranceDetails::find()
		->where('ProductId=:ProductId',[':ProductId'=>$product->ProductId])->andWhere('ClientId=:ClientId',[':ClientId'=>$cid] )->andWhere('Id=:Id',[':Id'=>$_GET['offerId']])->one();


		$latestDocuments= CardoenLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$myOffers['ClientId']] )->andWhere('ProductId=:ProductId',[':ProductId'=>$myOffers['ProductId']])->
		andWhere('offerId=:OfferId',[':OfferId'=>$myOffers['Id']])->one(); 

		return $this->render('/profile/generateinsurancepdfemail.php', [
			'model' => $vehicledata,
			'insurancerequest' =>$insuranceReq,
			'data' => $data,
			'mycategory'=>$myCategory,
			'allproduct' => $allproduct,
			'myoffers'=>$myOffers,
			'latestDocuments'=>$latestDocuments, 
			'category'=>$category,
			'cid'=>$cid,
			'productName'=>$productName,
			'productId'=>$productId,
		]);

	}
	
	
	
}