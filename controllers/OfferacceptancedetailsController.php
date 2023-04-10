<?php
namespace app\controllers;
use yii\db\QueryBuilder;
use Yii;
use yii\widgets\ListView;
use yii\data\Pagination;
use yii\widgets\LinkPager;
use app\models\Products;
use yii\web\Controller;
use app\models\InsuranceDetails;
use app\models\OfferAcceptanceDetails;
use app\models\CardoenOfferAcceptanceDetails;
use yii\data\ActiveDataProvider;

class OfferacceptancedetailsController extends Controller
{
	// offeracceptance for verschueren

	public function actionOfferacceptanceview(){
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}


		$vehicledata = InsuranceDetails::find()->where('ClientId=:ClientId',[':ClientId'=>$cid])->andWhere('Id=:Id',[':Id'=>$_GET['Id']])->one();
		$acceptancedetail = OfferAcceptanceDetails::find()->where('transactionId =:transactionId', [':transactionId' => $vehicledata->transaction_id])->andWhere('ProductId=:ProductId',[':ProductId'=>$vehicledata->ProductId])->orderBy(['id' =>SORT_DESC])->all();
  //$acceptancedetail = OfferAcceptanceDetails::find()->where('Id=:Id',[':Id'=>$_GET['Id']])->one();

	//$acceptancedetail = OfferAcceptanceDetails::find()->where('transactionId =:transactionId', [':transactionId' => $vehicledata->transaction_id])->orderBy(['id' =>SORT_DESC])->all();
		$count_val=count($acceptancedetail);
		$app = null;
		if($count_val>0)
		{
			$app=$acceptancedetail[0];
		}	
		return $this->render('/profile/offeracceptanceview.php', ['acceptancedetail' =>$acceptancedetail]);
	} 



	public function actionOfferacceptancegridview(){

		$acceptancedetail = OfferAcceptanceDetails::find()->where('Id=:Id',[':Id'=>$_GET['Id']])->one();

		$productId = $acceptancedetail['productId'];
		$product = Products::find()->where('ProductId = :ProductId',[':ProductId'=>$productId])->one();
		$productName = $product['DealerCode'];
		return $this->render('/profile/offeracceptanceview.php', ['acceptancedetail' =>$acceptancedetail,'productId'=>$productId,'productName'=>$productName]);
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
	
	public function actionOfferacceptancelist()
	{
		$productId=$_GET['productId'];
		$product = Products::find()->where('ProductId = :ProductId',[':ProductId'=>$productId])->one();
		$productName = $product['DealerCode'];
		$dataProvider = new ActiveDataProvider([        
			'query' => OfferAcceptanceDetails::find()->
			where('productId=:productId',[':productId'=>$productId])->
			orderBy(['id' =>SORT_DESC]),
			
			'pagination' => [ 'pageSize' => 10 ]	
			

		]);

		return $this->render('/offeracceptance/offeracceptancelist.php', [
			'dataProvider' => $dataProvider,'productId'=>$productId,'productName'=>$productName
		]);
		
		
	}
	
	


	//----cardoenofferacceptance	--
	
	public function actionCardoenofferacceptanceview(){
		
		if($this->isUserAdmin() && isset($_GET['cid'])){	 				
			$cid=$_GET['cid'];
		}else{
			$cid=Yii::$app->user->id;
		}

		$vehicledata = CardoenDetails::find()->where('ClientId=:ClientId',[':ClientId'=>$cid])->andWhere('Id=:Id',[':Id'=>$_GET['Id']])->one();
		$acceptancedetail = CardoenOfferAcceptanceDetails::find()->where('transactionId =:transactionId', [':transactionId' => $vehicledata->transaction_id])->orderBy(['id' =>SORT_DESC])->all();

		$count_val=count($acceptancedetail);
		$app = null;
		if($count_val>0)
		{
			$app=$acceptancedetail[0];
		}	
		return $this->render('/profile/cardoenofferacceptanceview.php', ['acceptancedetail' =>$acceptancedetail]);
	} 
	
	

	public function actionCardoenofferacceptancegridview(){

		$acceptancedetail = CardoenOfferAcceptanceDetails::find()->where('id=:id',[':id'=>$_GET['id']])->one();

		return $this->render('/profile/cardoenofferacceptanceview.php', ['acceptancedetail' =>$acceptancedetail]);
	} 
	
	
	
	
	public function actionCardoenofferacceptancelist()
	{

		$dataProvider = new ActiveDataProvider([
			'query' => CardoenOfferAcceptanceDetails::find(),



			'pagination' => [ 'pageSize' => 10]	

		]);

		return $this->render('/offeracceptance/cardoenofferacceptancelist.php', [
			'dataProvider' => $dataProvider
		]);
		
		
	}

	
}
