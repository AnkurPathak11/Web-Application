<?php


namespace app\controllers\bankruptcy;

use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\UserDetails;
use app\models\bankruptcy\BankRuptcyDetails;
use app\models\Clients;
use app\models\bankruptcy\CuratorDetails;
use app\models\bankruptcy\PreOfferBankRuptcy;
use app\models\bankruptcy\BankRuptcyReallocation;
use app\models\CardoenLatestDocuments;
use app\models\Products;
use app\controllers\helper\AzureStorage;
use yii\helpers\Html;
use app\controllers\helper\RestClientController;
use app\controllers\Config;
use app\models\UploadForm;
use app\models\PartnersDocuments;
use yii\web\UploadedFile;



class BankruptcyController extends Controller
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
			$this->partialrender('../bankruptcydashboard');
		}
		$productId = $_GET['productid'];
		$product = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();   
		$productName = $product['DealerCode'];
		$reportPath = $product['ReportPath'];

		$preofferbankruptcyQuery = PreOfferBankRuptcy::find()->orderBy(['transaction_id'=>SORT_DESC])->limit(10)->where('status is not null');

		$preofferbankruptcy = new ActiveDataProvider([
			'query' =>$preofferbankruptcyQuery,
			'pagination' => false

		]);


		$bankruptcydetailsQuery =  BankRuptcyDetails::find()->orderBy(['id'=>SORT_DESC])->limit(10)->where('status is not null');


		$bankruptcydetails = new ActiveDataProvider([
			'query' =>$bankruptcydetailsQuery,
			'pagination' => false

		]);

		$value = function ($data,$key,$index,$grid) {

			return Html::a(Html::encode($data->caseReferenceNumber),'/bankruptcy/bankruptcy/bankruptcydetails?preofferid='.$key);
		};

		$value1 = function ($data,$key,$index,$grid) {
		//print_r($key);
			return Html::a(Html::encode($data->CaseReferenceNumber),'/bankruptcy/bankruptcy/bankruptcydetails?preofferid='.$data->preOfferId);
		};

		$dateValue = function($data)
		{
			$date = date_create($data->createdDate);
			return date_format($date,"d/m/Y");

		};
		$createdDate = array('attribute'=>'CreatedDate','format'=>'text','label'=>'Datum','value'=>$dateValue);

		$companyName = array('attribute'=>'companyName','format'=>'text','label'=>'Bedrijfsnaam');

		$status = array('attribute'=>'status','format'=>'text','label'=>'Toestand'); 

		$premiumAmount = array('attribute'=>'premiumAmount','format'=>'text','label'=>'Premie'); 


		$contentOptions = array('style' => 'text-decoration-line: underline');

		$caseReferenceNumber = $this->gridColRendering('Case Reference Number','Zaaknummer','raw',$contentOptions,$value);

		$CaseReferenceNumber = $this->gridColRendering('Case Reference Number','Zaaknummer','raw',$contentOptions,$value1);

		$newCasesColData = array();
		$recentTransactionColData = array();
		$gridPanelArray = $this->gridViewPanel($caseReferenceNumber,$CaseReferenceNumber,$createdDate,$companyName,$premiumAmount,$status);

		$newCasesColData = $gridPanelArray['newCasesColData'];
		$recentTransactionColData = $gridPanelArray['recentTransactionColData'];

		$newCase = 0;
		$recentRequest = 1;

		return $this->render('../bankruptcydashboard', ['productId'=>$productId, 'productName'=>$productName,'newCasesDataProvider'=>$preofferbankruptcy,'recentTransactionDataProvider'=>$bankruptcydetails,'newCasesColData'=>$newCasesColData,'recentTransactionColData'=>$recentTransactionColData,'reportWithoutPivotPath'=>$reportPath,'newCase'=>$newCase,'recentRequest'=>$recentRequest]);
	}

	public function gridViewPanel($caseReferenceNumber,$CaseReferenceNumber,$createdDate,$companyName,$premiumAmount,$status)
	{
		$newCasesColData = 	array(
			$createdDate,
			$caseReferenceNumber,
			$companyName,
			$status);

		$recentTransactionColData = array(
			$createdDate,
			$CaseReferenceNumber,
			$premiumAmount,
			$status);

		return	$gridViewArray = array('newCasesColData'=>$newCasesColData,'recentTransactionColData'=>$recentTransactionColData);
	}

	public function gridColRendering($attribute,$label,$format,$contentOptions,$value)
	{


		$colRender = array('attribute'=>$attribute,'label'=>$label,'format'=>$format,'contentOptions'=>$contentOptions,'value'=>$value);

		return $colRender;

	}

	public function actionSearch(){	

		$data =  Yii::$app->request->post();

		if($data!= null){

			$caseReferenceNo = $data['refno'];	
			$companyName= $data['cmpname'];
			$query = new \yii\db\Query();

			$query->select(['preOfferBankruptcy.transaction_id', 'preOfferBankruptcy.caseReferenceNumber', 'preOfferBankruptcy.companyName',
				'preOfferBankruptcy.status']);
			$query->from('PreOfferBankRuptcy preOfferBankruptcy');
			$query->where('preOfferBankruptcy.caseReferenceNumber is not null');

			if(isset($caseReferenceNo)  && !empty($caseReferenceNo) ){  
				$query=$query->andWhere(['LIKE', 'preOfferBankruptcy.caseReferenceNumber','%'.$caseReferenceNo.'%',false]);
			}
			if(isset($companyName)  && !empty($companyName) ){
				$query=$query->andWhere(['LIKE', 'preOfferBankruptcy.companyName','%'.$companyName.'%',false]);
			}


			$query->orderBy(['preOfferBankruptcy.createdDate' =>SORT_ASC]);
			$preofferdetails = $query->all();
			$json = array('details'=>$preofferdetails);
			return json_encode($json);


		}
		else{
			$json=array('details'=> array());
			return json_encode($json);
		}

	}

	public function actionCasereallocate(){

		if(Yii::$app->user->getIsGuest()) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}

		if($this->isUserAdmin())
		{	
			$model = new BankRuptcyReallocation();

			if(Yii::$app->request->post()){

				$model->load(Yii::$app->request->post());
				$model->CreatedDate = date('m/d/Y h:i:s');
				$model->save(false);

				$preofferbankruptcy = PreOfferBankRuptcy::find()->where('transaction_id =:id', [':id' => $model->OfferId])->one();
				$this->updatePreOfferBankRuptcy($preofferbankruptcy);
				$newPreofferId = $this->saveNewPreOfferBankRuptcy($preofferbankruptcy, $model->CuratorId, $model->CaseExpiryDate);

				$this->sendReallocationEmail($newPreofferId);


				Yii::$app->session->setFlash('success', 'Case reallocated successfully.');
				return $this->redirect(['/site/success']);

			}	
			$id=$_GET['preofferid'];			 
			$preofferdetails = PreOfferBankRuptcy::find()->where('transaction_id =:id', [':id' => $id])->one();
			$model->CaseReferenceNumber = $preofferdetails->caseReferenceNumber;
			$productId = $preofferdetails['productId'];

			$query = new \yii\db\Query();
			$query->select(['curators.FullName','curators.id']);
			$query->from('CuratorDetails curators');
			$query->orderBy(['curators.FullName' =>SORT_ASC]);
			$curators = $query->all();

			return $this->render('/bankruptcy/casereallocate.php', [
				'preofferdetails' => $preofferdetails,
				'curators' => $curators,
				'model' => $model,
				'productId'=>$productId,
			]);
		}

		Yii::$app->session->setFlash('error', 'you are not authorised');
		return $this->redirect(['/site/unauthorised']);

	}

	public function actionOverview(){

		if(Yii::$app->user->getIsGuest()) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}	
		if($this->isUserAdmin())
		{
			$id=$_GET['preofferid'];

			$preOfferdetails = PreOfferBankRuptcy::find()->where('transaction_id =:id', [':id' => $id])->one();
			$preOfferId = $preOfferdetails->transaction_id;
			$bankruptcydetails = BankRuptcyDetails::find()->where('preOfferId =:id', [':id' => $preOfferId])->one();


			$bankruptcydetailsid = $bankruptcydetails->id;	
			$policyno = $bankruptcydetails->policyNo;

			return $this->render('/bankruptcy/overview.php', [
				'preofferbankruptcy' => $preOfferdetails,
				'id' => $bankruptcydetailsid,
				'policyno' => $policyno]);
		}	 

		Yii::$app->session->setFlash('error', 'you are not authorised');
		return $this->redirect(['/site/unauthorised']);
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

	public function actionGeneratepolicyandemail(){

		if(Yii::$app->user->getIsGuest()) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}

		if($this->isUserAdmin())
		{	 
			$preOfferId = $_GET['preofferid'];

			$bankruptcydetails = BankRuptcyDetails::find()->where('preOfferId =:id', [':id' => $preOfferId])->one();
			$id = -1;
			$latestDocuments = null;

			if($bankruptcydetails != null){

				$id = $bankruptcydetails->id;
				$latestDocuments= CardoenLatestDocuments::find()->where('ClientId=:ClientId',[':ClientId'=>$bankruptcydetails['ClientId']] )->andWhere('ProductId=:ProductId',[':ProductId'=>12])->
				andWhere('offerId=:OfferId',[':OfferId'=>$id])->one();
			}

			$product = Products::find()->where('ProductId =:ProductId', [':ProductId' => 12])->one();
			$productName = $product->DealerCode;
			$displayName = $product['DisplayName'];
			$productId = $product['ProductId'];


			return $this->render('/bankruptcy/generatepolicyandemail.php',
				['id' => $id,
				'latestDocuments' => $latestDocuments,
				'productName' => $productName,'displayName'=>$displayName,'productId'=>$productId]);
		}

		Yii::$app->session->setFlash('error', 'you are not authorised');
		return $this->redirect(['/site/unauthorised']);				

	}

	public function actionDownloadgeneratedfile(){

		$docType=$_GET['doctype'];
		$fileName = $docType;
		$productName = $_GET['productname'];

		return AzureStorage::azurestorage($docType,$fileName,$productName);

	}
	
	public function saveNewPreOfferBankRuptcy($preofferbankruptcy, $curatorId, $caseExpiryDate){

		
		$curatorDetails = CuratorDetails::find()->where('id=:id', [':id' => $curatorId])->one();

		$extractedData = $preofferbankruptcy->extractedData;
		$decodedData = json_decode($extractedData);
		
		$extractedCompanyDetails = $decodedData->companyDetails;
			//$extractedOfferDetails = $decodedData->offerDetails;
		$referenceNumber = $extractedCompanyDetails->referenceNumber;
		
		$newCompanyDetails = $this->setNewCompanyDetails($extractedCompanyDetails, $caseExpiryDate);
		
		$newLawyerDetails = stripslashes(json_encode(array('firstname' => $curatorDetails->FirstName,
			'lastname'=> $curatorDetails->LastName,'email_1'=> $curatorDetails->Email_1,
			'email_2'=> $curatorDetails->Email_2,'city'=> $curatorDetails->City,
			'number'=> $curatorDetails->HouseNo,'streetname'=> $curatorDetails->StreetName,
			'bus'=> $curatorDetails->Bus,'postal'=> $curatorDetails->PostalCode)));						  

		//	$newData = stripslashes(json_encode(array('companyDetails' => $extractedCompanyDetails,'lawyerDetails' => json_decode($newLawyerDetails), 'offerDetails' => $extractedOfferDetails)));

		$newData = stripslashes(json_encode(array('companyDetails' => $newCompanyDetails,'lawyerDetails' => json_decode($newLawyerDetails))));

		$createdDate = date('m/d/Y h:i:s');
		$preoffermodel= new PreOfferBankRuptcy();
		$preoffermodel->productId= $preofferbankruptcy->productId;
		$preoffermodel->offerDate= $preofferbankruptcy->offerDate;
			//$preoffermodel->offerStatus= $preofferbankruptcy->offerStatus;
		$preoffermodel->emailRefNo = base64_encode($referenceNumber.$createdDate);
		$preoffermodel->extractedData = $newData;
		$preoffermodel->remarks = $preofferbankruptcy->remarks;
		$preoffermodel->caseReferenceNumber = $preofferbankruptcy->caseReferenceNumber;
		$preoffermodel->companyName = $preofferbankruptcy->companyName;
		$preoffermodel->createdDate = $createdDate;
		$preoffermodel->save(false);
		
		return $preoffermodel->getPrimaryKey();

	}

	public function updatePreOfferBankRuptcy($preofferbankruptcy){

		$preofferbankruptcy->status = "Case Reallocated";
		$preofferbankruptcy->update();
		
	}

	public function sendReallocationEmail($id){

		$url = Config::getBaseUrl()."public/api/bankruptcy/preoffer/mail";			 
		$authorization = Config::getAuthorization();
		$newdata = array("transaction_id"=>$id);

		return RestClientController::invokePostApi($url, $authorization, $newdata);


	}	  
	public function actionBankruptcydetails(){

		if(Yii::$app->user->getIsGuest()) {
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}	
		if($this->isUserAdmin())
		{

			$model = new BankRuptcyDetails();
			$clientDetails = new Clients();

			if(Yii::$app->request->post())
			{

				$data = Yii::$app->request->post();

				$preofferid = $data['preOfferId'];

				if($preofferid != ''){

					$detailsid = $data['BankRuptcyDetails']['id'];
					$bankruptcydetails = BankRuptcyDetails::find()->where('id =:id', [':id' => $detailsid])->one();
					$this->updateBankruptcyDetails($bankruptcydetails, $data);

					$clientId = $bankruptcydetails->ClientId;				
					$client = Clients::find()->where('UserId =:UserId', [':UserId' => $clientId])->one();

					$this->updateClientDetails($client, $data);

					return $this->render('/bankruptcy/bankruptcydetails.php', [				
						'model' => $bankruptcydetails,
						'clientdetails' => $client,
						'productId'=>$model['ProductId']
					]);
				}

				else{

					$preOfferId = $data['id'];
					$preOfferbankruptcy = PreOfferBankRuptcy::find()->where('transaction_id =:id', [':id' => $preOfferId])->one();
					$this->updatePreOfferDetails($preOfferbankruptcy, $data);

					Yii::$app->session->setFlash('success', 'PreOffer Details updated successfully.');
					return $this->redirect(['/site/success']);

				}	


			}

			$preofferid=$_GET['preofferid'];
			$model = BankRuptcyDetails::find()->where('preOfferId =:preofferid', [':preofferid' => $preofferid])->one();

			if(isset($model)){
				$clientId = $model->ClientId;			 
				$clientDetails = Clients::find()->where('UserId =:UserId', [':UserId' => $clientId])->one();

				return $this->render('/bankruptcy/bankruptcydetails.php', [				
					'model' => $model,
					'clientdetails' => $clientDetails,
					'productId'=>$model['ProductId']
				]);
			}

			else {

				$model = new BankRuptcyDetails();

				$preOfferbankruptcy = PreOfferBankRuptcy::find()->where('transaction_id =:id', [':id' => $preofferid])->one();

				$extractedData = $preOfferbankruptcy->extractedData;
				// $model->preOfferId = $preOfferbankruptcy->transaction_id; 
				$this->validateAndSetExtractedData($model, $extractedData,$clientDetails);

				return $this->render('/bankruptcy/preofferbankruptcy.php',
					['model' =>$model,'clientdetails' => $clientDetails, 'preOfferbankruptcy' =>$preOfferbankruptcy,'productId'=>$preOfferbankruptcy['productId']]);

			}



		}

		Yii::$app->session->setFlash('error', 'you are not authorised');
		return $this->redirect(['/site/unauthorised']);		
	}


	public function saveNewBankruptcyDetails($bankruptcydetails, $newpreofferId){

		$newbankruptcydetails = new BankRuptcyDetails();

		$newbankruptcydetails->preOfferId = $newpreofferId;
		$newbankruptcydetails->mandateType= $bankruptcydetails->mandateType;
		$newbankruptcydetails->coverLimit = $bankruptcydetails->coverLimit;
		$newbankruptcydetails->paymentMethod = $bankruptcydetails->paymentMethod;
		$newbankruptcydetails->premiumAmount = $bankruptcydetails->premiumAmount;
		$newbankruptcydetails->paymentDate= $bankruptcydetails->paymentDate;
		$newbankruptcydetails->paymentRefNo= $bankruptcydetails->paymentRefNo;
		$newbankruptcydetails->policyNo = $bankruptcydetails->policyNo;
		$newbankruptcydetails->policyIssueDate = $bankruptcydetails->policyIssueDate;
		$newbankruptcydetails->status = $bankruptcydetails->status;
		$newbankruptcydetails->remarks = $bankruptcydetails->remarks;
		$newbankruptcydetails->BillingStreet = $bankruptcydetails->BillingStreet;
		$newbankruptcydetails->BillingNr = $bankruptcydetails->BillingNr;
		$newbankruptcydetails->BillingPostalCode = $bankruptcydetails->BillingPostalCode;
		$newbankruptcydetails->BillingBus = $bankruptcydetails->BillingBus;
		$newbankruptcydetails->BillingCity = $bankruptcydetails->BillingCity;
		$newbankruptcydetails->CompanyName = $bankruptcydetails->CompanyName;
		$newbankruptcydetails->CompanyStreet = $bankruptcydetails->CompanyStreet;
		$newbankruptcydetails->CompanyNr = $bankruptcydetails->CompanyNr;
		$newbankruptcydetails->CompanyPostalCode = $bankruptcydetails->CompanyPostalCode;
		$newbankruptcydetails->CompanyBus = $bankruptcydetails->CompanyBus;
		$newbankruptcydetails->CompanyCity = $bankruptcydetails->CompanyCity;
		$newbankruptcydetails->CaseReferenceNumber = $bankruptcydetails->CaseReferenceNumber;
		$newbankruptcydetails->CourtName = $bankruptcydetails->CourtName;
		$newbankruptcydetails->ClientId = $bankruptcydetails->ClientId;
		$newbankruptcydetails->BillingCompanyName = $bankruptcydetails->BillingCompanyName;
		$newbankruptcydetails->save(false);
	} 

	private function validateAndSetExtractedData($model,$extractedData,$clientDetails){

		$decodedData = json_decode($extractedData);

		$extractedCompanyDetails = $decodedData->companyDetails;
		$extractedLawyerDetails = $decodedData->lawyerDetails;

		if(isset($extractedCompanyDetails->referenceNumber)){
			$model->CaseReferenceNumber = $extractedCompanyDetails->referenceNumber;
		}

		if(isset($extractedCompanyDetails->name)){
			$model->CompanyName = $extractedCompanyDetails->name;
		}

		if(isset($extractedCompanyDetails->streetName)){
			$model->CompanyStreet = $extractedCompanyDetails->streetName;
		}

		if(isset($extractedCompanyDetails->streetNumber)){
			$model->CompanyNr = $extractedCompanyDetails->streetNumber;
		}

		if(isset($extractedCompanyDetails->bus)){
			$model->CompanyBus = $extractedCompanyDetails->bus;
		}

		if(isset($extractedCompanyDetails->city)){
			$model->CompanyCity = $extractedCompanyDetails->city;
		}

		if(isset($extractedCompanyDetails->postal)){
			$model->CompanyPostalCode = $extractedCompanyDetails->postal;
		}

		if(isset($extractedLawyerDetails->firstname)){
			$clientDetails->FirstName = $extractedLawyerDetails->firstname;
		}

		if(isset($extractedLawyerDetails->lastname)){
			$clientDetails->LastName = $extractedLawyerDetails->lastname;
		}

		if(isset($extractedLawyerDetails->email_1)){
			$clientDetails->Email = $extractedLawyerDetails->email_1;
		}

		if(isset($extractedLawyerDetails->streetname)){
			$clientDetails->Street = $extractedLawyerDetails->streetname;
		}

		if(isset($extractedLawyerDetails->number)){
			$clientDetails->Nr = $extractedLawyerDetails->number;
		}

		if(isset($extractedLawyerDetails->bus)){
			$clientDetails->Bus = $extractedLawyerDetails->bus;
		}

		if(isset($extractedLawyerDetails->city)){
			$clientDetails->City = $extractedLawyerDetails->city;
		}

		if(isset($extractedLawyerDetails->postal)){
			$clientDetails->PostalCode = $extractedLawyerDetails->postal;
		}

	}

	private function updateBankruptcyDetails($bankruptcydetails, $data){

		$bankruptcydetails->CompanyName =$data['BankRuptcyDetails']['CompanyName'];
		$bankruptcydetails->CompanyStreet =$data['BankRuptcyDetails']['CompanyStreet'];
		$bankruptcydetails->CompanyNr =$data['BankRuptcyDetails']['CompanyNr'];
		$bankruptcydetails->CompanyBus =$data['BankRuptcyDetails']['CompanyBus'];
		$bankruptcydetails->CompanyCity =$data['BankRuptcyDetails']['CompanyCity'];
		$bankruptcydetails->CompanyPostalCode =$data['BankRuptcyDetails']['CompanyPostalCode'];
		$bankruptcydetails->BillingStreet =$data['BankRuptcyDetails']['BillingStreet'];
		$bankruptcydetails->BillingNr =$data['BankRuptcyDetails']['BillingNr'];
		$bankruptcydetails->BillingBus =$data['BankRuptcyDetails']['BillingBus'];
		$bankruptcydetails->BillingCity =$data['BankRuptcyDetails']['BillingCity'];
		$bankruptcydetails->BillingPostalCode =$data['BankRuptcyDetails']['BillingPostalCode'];
		$bankruptcydetails->CaseDate = date('Y-m-d',strtotime(str_replace('/', '-',$data['BankRuptcyDetails']['CaseDate'])));

		$bankruptcydetails->update(false);



	}	

	private function updateClientDetails($client, $data){

		$client->FirstName = $data['Clients']['FirstName'];
		$client->LastName = $data['Clients']['LastName'];
		$client->Email = $data['Clients']['Email'];
		$client->Street = $data['Clients']['Street'];
		$client->Nr = $data['Clients']['Nr'];
		$client->Bus = $data['Clients']['Bus'];
		$client->City = $data['Clients']['City'];
		$client->PostalCode = $data['Clients']['PostalCode'];

		$client->update(false);


	}	

	private function updatePreOfferDetails($preOfferbankruptcy, $data){

		$newLawyerDetails = stripslashes(json_encode(array('firstname' => $data['Clients']['FirstName'],
			'lastname'=> $data['Clients']['LastName'],'streetname'=> $data['Clients']['Street'],'number'=> $data['Clients']['Nr'],'bus'=> $data['Clients']['Bus'],'postal'=> $data['Clients']['PostalCode'],'city'=> $data['Clients']['City'],'email_1'=> $data['Clients']['Email'])));						  

		$newCompanyDetails = stripslashes(json_encode(array('name' => $data['BankRuptcyDetails']['CompanyName'],'referenceNumber' => $data['BankRuptcyDetails']['CaseReferenceNumber'],'streetName' => $data['BankRuptcyDetails']['CompanyStreet'],'city' => $data['BankRuptcyDetails']['CompanyCity'],'bus' => $data['BankRuptcyDetails']['CompanyBus'],'streetNumber' => $data['BankRuptcyDetails']['CompanyNr'],'postal' => $data['BankRuptcyDetails']['CompanyPostalCode'])));

		$newData = stripslashes(json_encode(array('companyDetails' => json_decode($newCompanyDetails),'lawyerDetails' => json_decode($newLawyerDetails))));


		$preOfferbankruptcy->extractedData = $newData;


		$preOfferbankruptcy->update(false);

	}

	private function setNewCompanyDetails($extractedCompanyDetails, $caseExpiryDate){

		if(isset($extractedCompanyDetails->name)){
			$companyName = $extractedCompanyDetails->name;
		}
		if(isset($extractedCompanyDetails->address)){
			$companyAddress = $extractedCompanyDetails->address;
		}

		if(isset($extractedCompanyDetails->referenceNumber)){
			$caseReferenceNumber = $extractedCompanyDetails->referenceNumber;
		}

		if(isset($extractedCompanyDetails->date)){
			$caseDate = $extractedCompanyDetails->date;
		}

		if(isset($extractedCompanyDetails->court)){
			$court = $extractedCompanyDetails->court;
		}

		if(isset($extractedCompanyDetails->streetName)){
			$companyStreet = $extractedCompanyDetails->streetName;
		}

		if(isset($extractedCompanyDetails->streetNumber)){
			$companyNr = $extractedCompanyDetails->streetNumber;
		}

		if(isset($extractedCompanyDetails->bus)){
			$companyBus = $extractedCompanyDetails->bus;
		}

		if(isset($extractedCompanyDetails->postal)){
			$companyPostalCode = $extractedCompanyDetails->postal;
		}

		if(isset($extractedCompanyDetails->city)){
			$companyCity = $extractedCompanyDetails->city;
		}

		$newCompanyDetails = stripslashes(json_encode(array('name' => $companyName,
			'address'=> $companyAddress,'referenceNumber'=> $caseReferenceNumber,
			'date'=> $caseDate,'court'=> $court,
			'streetName'=> $companyStreet,'streetNumber'=> $companyNr,
			'bus'=> $companyBus,'postal'=> $companyPostalCode,'city'=> $companyCity,
			'offerValidDate'=> $caseExpiryDate)));
		
		return json_decode($newCompanyDetails);

	}

	public function actionUpdateCuratorDetails()
	{
		$model = new UploadForm();

		$partnerDoc = new PartnersDocuments();
		$productId = $_GET['productId'];
		
			// $spreadsheet = new Spreadsheet();

		if(Yii::$app->request->ispost)
		{

			$directory = '..\files\uploads\documents\bankruptcy\\';
				// $path = realPath(Yii::$app->basePath).'\\'.$directory;
			$path = $directory;
			$model->excelFile = UploadedFile::getInstance($model, 'excelFile');
			$model->fileupload($directory);

			$partnerDoc->upload_dir = $path;

			$partnerDoc->document_format =$model->excelFile->extension;
			$partnerDoc->document_type = strtoupper('promandato_'.$model->excelFile->extension);

			$name = uniqid(rand());

			$partnerDoc->uniqueFile_name = $name.'.'.$model->excelFile->extension;
			$partnerDoc->created_date =  date('Y-m-d H:i:s');
			$partnerDoc->file_name = $model->excelFile->baseName.'.'.$model->excelFile->extension;

			$partnerDoc->save(false);
			
				// For saving file data into curator details

				// Loading Excel file 
			$file =  $path.$partnerDoc->file_name;
			$fileType = \PhpOffice\PhpSpreadsheet\IOFactory::identify($file);;

			$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($fileType);
			$spreadsheet = $reader->load($file);

			$sheetData = $spreadsheet->getActiveSheet()->toArray(); 

			$membershipNoIndex = array_column($sheetData, 2);
			$fileNo =  array_column($sheetData, 1);		
			$sheetLength = count($membershipNoIndex)-1;
				// Saving into database
			

				// End of saving data

				// $start = microtime(true);

			for($i=2; $i<$sheetLength;$i++)
			{
				$sheetRow = $sheetData[$i];
				$curatorFlag = false;

				$membershipNoVal = trim($membershipNoIndex[$i]," ");
				$fileNoVal = trim($fileNo[$i]," ");

				if(isset($membershipNoVal)  || isset($fileNoVal))
				{
					if($membershipNoVal == null)
					{
						$curator = CuratorDetails::find()->where('FileNo = :FileNo',[':FileNo'=>$fileNo[$i]])->one();
						
					}
					else
					{
						$curator = CuratorDetails::find()->where('MembershipNo = :MembershipNo',[':MembershipNo'=>$membershipNoIndex[$i]])->one();
					}

					if($curator == null)
					{
						$curatorFlag=true;
						$curator = new CuratorDetails();									
					}

					if(isset($sheetRow[1]))
					{
						$curator->FileNo = trim($sheetRow[1]," ");
					}
					if(isset($sheetRow[2]))
					{
						$curator->MembershipNo = trim($sheetRow[2]," ");
					}
					if(isset($sheetRow[3]))
					{
						$firstname =  substr($sheetRow[3],strrpos($sheetRow[3]," ")+1);	
						$lastname = substr($sheetRow[3],0,strrpos($sheetRow[3]," "));
						$curator->FirstName = $firstname;
						$curator->LastName = $lastname;
						$curator->FullName = trim($sheetRow[3]," ");
					}
					if(isset($sheetRow[4]))
					{
						$curator->StreetName = trim($sheetRow[4]," ");
					}
					if(isset($sheetRow[5]))
					{
						$curator->HouseNo = trim($sheetRow[5]," ");
					}
					if(isset($sheetRow[6]))
					{
						$curator->Bus = trim($sheetRow[6]," ");
					}
					if(isset($sheetRow[8]))
					{
						$curator->PostalCode = trim($sheetRow[8]," ");
					}
					if(isset($sheetRow[9]))
					{
						$curator->City = trim($sheetRow[9]," ");
					}
					if(isset($sheetRow[10]))
					{
						$curator->Country = trim($sheetRow[10]," ");
					}
					if(isset($sheetRow[11]))
					{
						$curator->Email_1 = trim($sheetRow[11]," ");
					}
					if(isset($sheetRow[12]))
					{
						$curator->Email_2 =trim($sheetRow[12]," ");	
					}
					if($curatorFlag==true)
					{
						$curator->save(false);
					}
					else
					{
						$curator->update(false);
					}
				}
			}
				// $time_elapsed_secs = microtime(true) - $start;
				// var_dump("Time Taken",$time_elapsed_secs);

			Yii::$app->session->setFlash('success', 'File Uploaded!');
			return $this->redirect(['/bankruptcy/bankruptcy/dashboard?productid='.$productId]);
		}


		return $this->render('/bankruptcy/updatecuratordetails.php',['model'=>$model,'productId'=>$productId]);

	}

	public function actionReportPage($data,$productId)
	{

		$col = 0;

		$product = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();   
		$productName = $product['DealerCode'];
		$displayName = $product['DisplayName'];

		$contentOptions = array('style' => 'text-decoration-line: underline');
		$dateValue = function($data)
		{
			$date = date_create($data->createdDate);
			return date_format($date,"d/m/Y");

		};
		$createdDate = array('attribute'=>'CreatedDate','format'=>'text','label'=>'Datum','value'=>$dateValue);

		$companyName = array('attribute'=>'companyName','format'=>'text','label'=>'Bedrijfsnaam');

		$status = array('attribute'=>'status','format'=>'text','label'=>'Toestand'); 

		$premiumAmount = array('attribute'=>'premiumAmount','format'=>'text','label'=>'Premie'); 
		$value = function ($data,$key,$index,$grid) {

			return Html::a(Html::encode($data->caseReferenceNumber),'/bankruptcy/bankruptcy/bankruptcydetails?preofferid='.$key);					
		};

		$value1 = function ($data,$key,$index,$grid) {

			return Html::a(Html::encode($data->CaseReferenceNumber),'/bankruptcy/bankruptcy/bankruptcydetails?preofferid='.$data->preOfferId);
		};

		$caseReferenceNumber = $this->gridColRendering('Case Reference Number','Zaaknummer','raw',$contentOptions,$value);
		$CaseReferenceNumber = $this->gridColRendering('Case Reference Number','Zaaknummer','raw',$contentOptions,$value1);

		$gridPanelArray = $this->gridViewPanel($caseReferenceNumber,$CaseReferenceNumber,$createdDate,$companyName,$premiumAmount,$status);

		if($data==0)
		{
			$preofferbankruptcyQuery = PreOfferBankRuptcy::find()->orderBy(['transaction_id'=>SORT_DESC])->where('status is not null');

			$preofferbankruptcy = new ActiveDataProvider([
				'query' =>$preofferbankruptcyQuery,
				'pagination' => false
			]);



			$newCasesColData = array();
			$newCasesColData = $gridPanelArray['newCasesColData'];

			return $this->render('/common/viewallreport.php',['newCasesDataProvider'=>$preofferbankruptcy,'newCasesColData'=>$newCasesColData,'col'=>$col,'productName'=>$productName,'productId'=>$productId,'displayName'=>$displayName]);

		}
		if($data==1)
		{
			$bankruptcydetailsQuery =  BankRuptcyDetails::find()->orderBy(['id'=>SORT_DESC])->where('status is not null');


			$bankruptcydetails = new ActiveDataProvider([
				'query' =>$bankruptcydetailsQuery,
				'pagination' => false
			]);


			$recentTransactionColData = array();



			$recentTransactionColData = $gridPanelArray['recentTransactionColData'];

			return $this->render('/common/viewallreport.php',['recentTransactionDataProvider'=>$bankruptcydetails,'recentTransactionColData'=>$recentTransactionColData,'col'=>$col,'productId'=>$productId,'productName'=>$productName,'displayName'=>$displayName]);

		}

	}

}	