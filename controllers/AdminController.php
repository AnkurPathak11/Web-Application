<?php

namespace app\controllers;

use Yii;
use yii\helpers\Html;
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
use app\models\CardoenDetails;
use app\models\InsuranceDetails;
use app\models\InsuranceRequest;
use yii\data\ActiveDataProvider;

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
		
	//$clientsdetails=Clients::find()->all();
	//$clientsdetails=Clients::find()->where(['not',['transaction_id' => null]])->orderBy(['FirstName' =>SORT_ASC,'LastName'=>SORT_ASC])->all();
		$clientsdetails = CardoenDetails::findBySql("select clients.UserId as ClientId ,clients.FirstName,clients.LastName,details.status,details.transaction_id ,clients.Email ,details.Id from CardoenDetails as details left JOIN  Clients as clients on clients.UserId=details.ClientId where details.status is not null ORDER BY clients.FirstName ASC, clients.LastName ASC")->all();
		return $this->render('dashboard',['clientsdetails' =>$clientsdetails]);
	}
	
	public function actionDashboardverschueren(){	
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
			$this->partialrender('dashboardverschueren');
		}
		
		$query = new \yii\db\Query();
		$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
		$query->from('InsuranceDetails details');
		$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
		$query->where('details.status is not null');
		$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
		$clientsdetails = $query->all();
		
		return $this->render('dashboardverschueren',['clientsdetails' =>$clientsdetails]);
	}
	
	public function actionDashboardbeerens(){	
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
			$this->partialrender('dashboardbeerens');
		}
		
		$query = new \yii\db\Query();
		$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
		$query->from('InsuranceDetails details');
		$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
		$query->where('details.status is not null');
		$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
		$clientsdetails = $query->all();
		$product = Products ::findBySql("select ReportPath from Products where ProductId = 8")->one();   
		$reportPath = $product['ReportPath'];
		return $this->render('dashboardbeerens',['clientsdetails' =>$clientsdetails, 'reportPath' =>$reportPath]);
	}
	
	public function actionDashboardcardoennewoffer(){	
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
			$this->partialrender('dashboardcardoennewoffer');
		}
		
		$query = new \yii\db\Query();
		$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
		$query->from('InsuranceDetails details');
		$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
		$query->where('details.status is not null');
		$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
		$clientsdetails = $query->all();
		
//	$clientsdetails = InsuranceDetails::findBySql("select clients.UserId as ClientId,clients.FirstName,clients.LastName,details.status,details.transaction_id ,clients.Email ,details.Id from InsuranceDetails as details left JOIN  Clients as clients on clients.UserId=details.ClientId where details.status is not null and clients.transaction_id is not null ORDER BY clients.FirstName ASC, clients.LastName ASC")->all();
//print_r($clientsdetails);
//exit(0);
		return $this->render('dashboardcardoennewoffer',['clientsdetails' =>$clientsdetails]);
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
	
	public function actionDashboard1(){
		$clientsdetails=Clients::find()->where(['not',['transaction_id' => null]])->orderBy(['FirstName' =>SORT_ASC,'LastName'=>SORT_ASC])->all();
		return $this->render('dashboard1',['clientsdetails' =>$clientsdetails]);
	}

	public function actionSearchit(){	
		$data =  Yii::$app->request->post();
		if ($data != null)
		{
			
		//$query =Clients::find()->where(['not',['transaction_id' => null]]);
			$query = new \yii\db\Query();
			$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
			$query->from('CardoenDetails details');
			$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
			$query->where('details.status is not null');

			
			if(isset($data['frstname'])  && !empty($data['frstname']) ){
		//$query=$query->andWhere(['LIKE', 'FirstName',$data['frstname'].'%',false]);
				
				$query=$query->andWhere(['LIKE', 'clients.FirstName',$data['frstname'].'%',false]);
			}
			if(isset($data['secondname'])  && !empty($data['secondname']) ){
		//$query=$query->andWhere(['LIKE', 'LastName',$data['secondname'].'%',false]);
				
				$query=$query->andWhere(['LIKE', 'clients.LastName',$data['secondname'].'%',false]);
			}
			
			$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
			$clientsdetails = $query->all();
			$json = array('clients'=>$clientsdetails);
			return json_encode($json);
			
		//		$clientsdetails =$query->orderBy(['FirstName' =>SORT_ASC,'LastName'=>SORT_ASC])->all();
		 //$clientsdetails = CardoenDetails::findBySql($queryForsearch)->all(); 
			
			
			/* $array = array();
			foreach($clientsdetails as $client){
				array_push($array, ['FirstName'=>$client->FirstName, 
									'LastName'=>$client->LastName,
									'UserId'=>$client->ClientId,
									'Email'=>$client->Email,
									'status'=>$client->status,
									'transaction_id'=>$client->transaction_id,
									'offerId'=>$client->Id]);
			}  
			$json=array('clients'=>$array);
			return json_encode($json); */
		}
		else{
			$json=array('clients'=> array());
			return json_encode($json);
		}
		
	}
	
	public function actionSearchverschueren(){	
		$data =  Yii::$app->request->post();
		if ($data != null)
		{
		//$productid = $data['productid'];
		//$query =Clients::find()->where(['not',['transaction_id' => null]]);
			$query = new \yii\db\Query();
			$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
			$query->from('InsuranceDetails details');
			$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
			$query->where('details.ProductId =9 and details.Status is not null');			
			
			if(isset($data['frstname'])  && !empty($data['frstname']) ){
		//$query=$query->andWhere(['LIKE', 'FirstName',$data['frstname'].'%',false]);
				
				$query=$query->andWhere(['LIKE', 'clients.FirstName',$data['frstname'].'%',false]);
			}
			if(isset($data['secondname'])  && !empty($data['secondname']) ){
		//$query=$query->andWhere(['LIKE', 'LastName',$data['secondname'].'%',false]);
				
				$query=$query->andWhere(['LIKE', 'clients.LastName',$data['secondname'].'%',false]);
			}
			
			$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
			$clientsdetails = $query->all();
			$json = array('clients'=>$clientsdetails);
			return json_encode($json);
			
		}
		else{
			$json=array('clients'=> array());
			return json_encode($json);
		}
		
	}

	public function actionSearchbeerens(){	
		$data =  Yii::$app->request->post();
		if ($data != null)
		{
			
		//$query =Clients::find()->where(['not',['transaction_id' => null]]);
			$query = new \yii\db\Query();
			$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
			$query->from('InsuranceDetails details');
			$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
			$query->where('details.ProductId =8 and details.Status is not null');			
			
			if(isset($data['frstname'])  && !empty($data['frstname']) ){
		//$query=$query->andWhere(['LIKE', 'FirstName',$data['frstname'].'%',false]);
				
				$query=$query->andWhere(['LIKE', 'clients.FirstName',$data['frstname'].'%',false]);
			}
			if(isset($data['secondname'])  && !empty($data['secondname']) ){
		//$query=$query->andWhere(['LIKE', 'LastName',$data['secondname'].'%',false]);
				
				$query=$query->andWhere(['LIKE', 'clients.LastName',$data['secondname'].'%',false]);
			}
			
			$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
			$clientsdetails = $query->all();
			$json = array('clients'=>$clientsdetails);
			return json_encode($json);
			
		}
		else{
			$json=array('clients'=> array());
			return json_encode($json);
		}
		
	}
	
	public function actionSearchcardoen(){	
		$data =  Yii::$app->request->post();
		if ($data != null)
		{
		//$productid = $data['productid'];
		//$query =Clients::find()->where(['not',['transaction_id' => null]]);
			$query = new \yii\db\Query();
			$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
			$query->from('InsuranceDetails details');
			$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
			$query->where('details.ProductId =4 and details.Status is not null');			
			
			if(isset($data['frstname'])  && !empty($data['frstname']) ){
		//$query=$query->andWhere(['LIKE', 'FirstName',$data['frstname'].'%',false]);
				
				$query=$query->andWhere(['LIKE', 'clients.FirstName',$data['frstname'].'%',false]);
			}
			if(isset($data['secondname'])  && !empty($data['secondname']) ){
		//$query=$query->andWhere(['LIKE', 'LastName',$data['secondname'].'%',false]);
				
				$query=$query->andWhere(['LIKE', 'clients.LastName',$data['secondname'].'%',false]);
			}
			
			$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
			$clientsdetails = $query->all();
			$json = array('clients'=>$clientsdetails);
			return json_encode($json);
			
		}
		else{
			$json=array('clients'=> array());
			return json_encode($json);
		}
		
	}
	
	
	public function actionRedirectdash(){	
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
			$this->partialrender('redirectdash');
		}
		
		return $this->render('redirectdash',[]);
	}
	
	
	public function actionDashboardnew(){	
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
			$this->partialrender('dashboardnew');
		}
		
		$productId = $_GET['productid'];
		$product = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();   
		$productName = $product['DealerCode'];
		$reportPath = $product['ReportPath'];

		$reportWithoutPivotPath = Yii::$app->params['reportWithoutPivotPath'];

		$insureacerequestQuery = InsuranceRequest::find()->where('CarDealer=:CarDealer',[':CarDealer'=>$productName])->andwhere('CreatedDate is not null')->limit(10)->orderby(['CreatedDate'=>SORT_DESC]);

		$insuracedetailsQuery = InsuranceDetails::find()->where('CarDealer=:CarDealer',[':CarDealer'=>$productName])->andwhere('PolicyEffectiveDate is not null')->limit(10)->orderby(['PolicyEffectiveDate'=>SORT_DESC]);


		$insuranceRequest = new ActiveDataProvider([
			'query' =>$insureacerequestQuery,
			'pagination'=> ['pageSize' => 10,]
		]);

		$insuranceDetails = new ActiveDataProvider([
			'query' =>$insuracedetailsQuery,
			'pagination'=> ['pageSize' => 10,]
		]);


		$nameValue = function ($data) {

			return $data->PolicyHolderFirstName.' '.$data->PolicyHolderLastName;
		};

		$contentOptions = array('style' => 'text-decoration-line: underline');


		$createdDateValue = function($data)
		{
			$date = date_create($data->CreatedDate);
			return date_format($date,"d/m/Y");

		};
		$createdDate = array('attribute'=>'CreatedDate','format'=>'text','label'=>'Datum','value'=>$createdDateValue);

		$name = array('attribute'=>'Name','format'=>'text','label'=>'Naam','value'=>$nameValue);

		$policyDateValue = function($data){
			$date = date_create($data->PolicyEffectiveDate);
			return date_format($date,"d/m/Y");

		};

		$policyDate = array('attribute'=>'PolicyEffectiveDate','format'=>'text','label'=>'Datum','value'=>$policyDateValue);

		$chassisNumber = array('attribute'=>'VehicleChassisNumber','format'=>'text','label'=>'Chassisnummer');

		$value = function ($data,$key) {

			return Html::a(Html::encode($data->ClientId),'/profile/overview?cid='.$data->ClientId.'&offertype='.$data->CarDealer);
		};

		$licenseDate = array('attribute'=>'PolicyHolderDrivingLicenceDate','format'=>'text','label'=>'Licentiedatum');

		$status = array('attribute'=>'Status','format'=>'text','label'=>'Toestand');

		$cid = $this->gridColRendering('cid','Client Id','raw',$contentOptions,$value);

		$vehiclePrice = array('attribute'=>'VehiclePrice','format'=>'text','label'=>'Voertuigprijs');

		$policyNo = array('attribute'=>'PolicyNo','format'=>'text','label'=>'Polisnummer'); 

		$netPremium = array('attribute'=>'NetPremium','format'=>'text','label'=>'Premie');

		$newCasesColData = array();
		$recentTransactionColData = array();
		$gridPanelArray = $this->gridViewPanel($name,$createdDate,$licenseDate,$policyDate,$chassisNumber,$cid,$status,$vehiclePrice,$policyNo,$netPremium);

		$newCasesColData = $gridPanelArray['newCasesColData'];
		$recentTransactionColData = $gridPanelArray['recentTransactionColData'];


		return $this->render('dashboardnew',['productId'=>$productId, 'productName' =>$productName,'reportPath'=>$reportPath,'reportWithoutPivotPath'=>$reportWithoutPivotPath,'newCasesDataProvider'=>$insuranceRequest,'recentTransactionDataProvider'=>$insuranceDetails,'newCasesColData'=>$newCasesColData,'recentTransactionColData'=>$recentTransactionColData]);
	}

	public function gridViewPanel($name,$createdDate,$licenseDate,$policyDate,$chassisNumber,$cid,$status,$vehiclePrice,$policyNo,$netPremium)
	{
		$newCasesColData =   array(
			$createdDate,
			$name,
			$licenseDate,
			$chassisNumber,
			$status);

		$recentTransactionColData = array(
			$policyDate,
			$policyNo,
			$cid,
			$vehiclePrice,
			$netPremium);

		return $gridViewArray = array('newCasesColData'=>$newCasesColData,'recentTransactionColData'=>$recentTransactionColData);
	}


	public function gridColRendering($attribute,$label,$format,$contentOptions,$value)
	{


		$colRender = array('attribute'=>$attribute,'label'=>$label,'format'=>$format,'contentOptions'=>$contentOptions,'value'=>$value);

		return $colRender;

	}

	public function actionSearchclient(){	
		$data =  Yii::$app->request->post();
		if ($data != null)
		{

		//$query =Clients::find()->where(['not',['transaction_id' => null]]);
			$query = new \yii\db\Query();
			$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
			$query->from('InsuranceDetails details');
			$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
		//$query->where('details.ProductId ='.$data['productid'].' and details.Status is not null');			
			$query->where('details.Status is not null and details.ProductId ='.$data['productid']);
			if(isset($data['frstname'])  && !empty($data['frstname']) ){
		//$query=$query->andWhere(['LIKE', 'FirstName',$data['frstname'].'%',false]);

				$query=$query->andWhere(['LIKE', 'clients.FirstName',$data['frstname'].'%',false]);
			}
			if(isset($data['secondname'])  && !empty($data['secondname']) ){
		//$query=$query->andWhere(['LIKE', 'LastName',$data['secondname'].'%',false]);

				$query=$query->andWhere(['LIKE', 'clients.LastName',$data['secondname'].'%',false]);
			}

			$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
			$clientsdetails = $query->all();
			$json = array('clients'=>$clientsdetails);
			return json_encode($json);

		}
		else{
			$json=array('clients'=> array());
			return json_encode($json);
		}

	}

	public function actionDashboardtest(){	
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
			$this->partialrender('dashboardtest');
		}

		$productId = 8;
		$product = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();   
		$productName = $product['DealerCode'];

	/*$query = new \yii\db\Query();
	$query->select(['clients.UserId AS ClientId','clients.FirstName','clients.LastName','details.status', 'details.transaction_id', 'clients.Email','details.Id']);
	$query->from('InsuranceDetails details');
	$query->leftjoin('Clients clients','clients.UserId=details.ClientId');
	$query->where('details.Status is not null and details.ProductId ='.$productId);
	$query->orderBy(['clients.FirstName' =>SORT_ASC,'clients.LastName'=>SORT_ASC]);
	$clientsdetails = $query->all();*/
	
	
	return $this->render('dashboardtest',['productId' =>$productId, 'productName' =>$productName]);
}



}