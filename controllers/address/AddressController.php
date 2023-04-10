<?php

namespace app\controllers\address;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\models\Streets;
use app\models\Products;


class AddressController extends Controller
{
	public function actionAddstreet(){

		if(Yii::$app->user->getIsGuest()){
			$session=Yii::$app->session;
			$session->set('url',$_SERVER['REQUEST_URI']);
			return $this->redirect(['register/login']);
		}
		if($this->isUserAdmin())
		{

			$model = new Streets();

			$query = new \yii\db\Query();
			$query->select(['PostalCode']);
			$query->from('AddressMaster addressMaster');
			$addressdata = $query->all();

			if(Yii::$app->request->post()){

				$data= Yii::$app->request->post();
				$streetName = $data['StreetName'];
				$postalCode = $data['Streets']['PostalCode'];

				$streetdata = Streets::find()->where('StreetName =:StreetName', [':StreetName' => $streetName])->andWhere('PostalCode=:PostalCode',[':PostalCode'=>intval($postalCode)])->one();
				

				if(!empty($streetdata)){
					if(count(array($streetdata))>0){
						$msg = "Street already exist";
						return $this->render('../addstreet',['model'=>$model,'postalcodes'=>$addressdata, 'msg'=>$msg]);
					}
				}
				else{			   
					$model->PostalCode = intval($postalCode);
					$model->StreetName = $streetName;
					
					$model->save(false);
					Yii::$app->session->setFlash('success', 'Address added successfully.');
					return $this->redirect(['/site/success']);
				}
			}
			$productId = $_GET['productId'];
			$product = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();
			$productName = $product['DealerCode'];

			$msg = "";
			return $this->render('../addstreet',['model'=>$model,'postalcodes'=>$addressdata, 'msg'=>$msg,'productId'=>$productId,'productName'=>$productName]);

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
	
	



}