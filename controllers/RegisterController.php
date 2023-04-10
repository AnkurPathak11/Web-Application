<?php

namespace app\controllers;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Clients;
use app\models\Forgotpass;
use app\models\Changepass;
use app\models\UserDetails;
use app\models\Products;
use app\models\MyProducts;
use app\models\AuthAssignment;
use app\modules\cargoinsurance\helper\UserService;
use app\models\ClientDetails;

class RegisterController extends Controller
{

    //  public function actionLogin()
    // {	


    //     $session=Yii::$app->session;


    //     if (!Yii::$app->user->isGuest) {
    //         if ($session->isActive && $session->has('url')){
    //             $url=$session->get('url');
    //             return $this->redirect([$url]);
    //         } else {
    //             return $this->goHome();
    //         }
    //     }

    //     $model = new LoginForm();

    //     if ($model->load(Yii::$app->request->post()) && $model->login()) {

    //         if ($session->isActive && $session->has('url')){
    //             $url=$session->get('url');

    //             return $this->redirect([$url]);
    //         } else {
				// $getRolesByUser=Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
				// if(is_array($getRolesByUser))
				// $role=array_keys($getRolesByUser)[0];
				// if(strcmp($role,'admin')==0)
				// {
				// //return $this->redirect(['/admin/dashboard']);
				// return $this->redirect(['/admin/redirectdash']);
				// }
				// else
				// {
				// return $this->redirect(['profile/overview']);	
				// }

    //         }
    //     }
    //     return $this->render('loginform', [
    //         'model' => $model,
    //     ]);
    // } 

	public function actionLogin()
	{	
		
		$model = new LoginForm();
		$session=Yii::$app->session;
		
		if (!Yii::$app->user->isGuest) {
			if ($session->isActive && $session->has('url')){
				$url=$session->get('url');
				return $this->redirect($url);
			} else {
				return $this->redirect(['/register/login']);
			}
		}
		
		if ($model->load(Yii::$app->request->post()) && $model->login()) {

			if ($session->isActive && $session->has('url')){
				$url=$session->get('url');
				
				return $this->redirect($url);
			} else {
				$getRolesByUser=Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
				if(is_array($getRolesByUser))
					$role=array_keys($getRolesByUser)[0];
				if(strcmp($role,'admin')==0 || strcmp($role,'cib_backoffice_admin')==0)
				{
					return $this->redirect(['/admin/redirectdash']);
				}

				if(strcmp($role,'cargo_backoffice_admin')==0 || strcmp($role,'cargo_supervisor')==0 )
				{

					$userId = UserService::getUserId();
					$clientsdetails = ClientDetails::find()->where(['ClientId'=>$userId])->one();
					$resetPassword = $clientsdetails['ResetPassword'];
					$isUserActive = UserService::IsUserActiveLogin($userId);
					if($resetPassword==true)
					{
						return $this->redirect(['/register/changepass','id'=>$userId]);
					}
					if($resetPassword==false && $isUserActive==true)
					{
						return $this->redirect(['/cargoinsurance/cargo-request-v3']);
					}
					else{
						Yii::$app->user->logout();
						return $this->redirect(['/register/login']);
					}
				}
				else
				{

					Yii::$app->user->logout();
					return $this->redirect(['/register/login']);
					//return $this->goHome();
				}
				
			}
		}
		return $this->render('loginform', [
			'model' => $model,
		]);
	}

	public function actionRegister(){
		$model = new Clients();
		if ($model->load(Yii::$app->request->post())) {
			
			$model->Password=sha1(Yii::$app->request->post('Clients')['Password']);
			$model->Repeatnewpass=sha1(Yii::$app->request->post('Clients')['Repeatnewpass']);
			if($model->Repeatnewpass==$model->Password){
				$model->save(false);
				if($model->save(false)){
					if(isset($model->UserId))
					{

						$this->assignRoleTOUser($model);
						$userId=$model->UserId;
						$userfullName=strtoupper($model->LastName.$model->FirstName);
						$refrenceName=substr($userfullName, 0, 3);
						$refrenceNo=1000000+intval($userId);
						$customerRefNumber=$refrenceName.$refrenceNo; 
						$model->CustomerRefNo=$customerRefNumber;
						$model->update();
					}
					return $this->redirect(['register/login']);
				}
			}else{
				Yii::$app->session->setFlash('error', "wachtwoord moet hetzelfde zijn");
			}
		}

		return $this->render('registration', [
			'model' => $model,
		]);
	}   

	
	public function actionForgotpass(){
		
		
		$model = new Forgotpass();
		if ($model->load(Yii::$app->request->post())) {
			$Email=Yii::$app->request->post('Forgotpass')['Email'];
			$model->forgot($Email);
			$customer = Clients::find()->where(['Email' => $Email])->one();
			if(!empty($customer)){
				Yii::$app->session->setFlash('success', 'Controleer uw e-mail om uw wachtwoord te wijzigen.');

				return $this->redirect(['/site/success']);
			}else{
				Yii::$app->session->setFlash('error','Controleer dan uw e-mail. ');
			}}
			return $this->render('forgotpass', [
				'model' => $model,
			]);
		}

		public function actionChangepass(){
			$model = new Clients();
			if ($model->load(Yii::$app->request->post())) {
				$password=sha1(Yii::$app->request->post('Clients')['Password']);
				$Repeatnewpass=sha1(Yii::$app->request->post('Clients')['Repeatnewpass']);
				if($Repeatnewpass==$password){
					$id=$_GET['id'];
					$connection = Yii::$app->db;
					$connection->createCommand()->update('Clients', ['Password' => $password], 'UserId = '.$id.'')->execute();
					return $this->redirect(['register/login']);
				}else{
					Yii::$app->session->setFlash('error', "wachtwoord moet hetzelfde zijn");
				}
			}
			return $this->render('changepass',[
				'model' => $model
			]);
		}


		public function assignRoleTOUser($model)
		{
			$authAssign=new AuthAssignment();
			$authAssign->user_id=(string)$model->UserId;
			$authAssign->item_name='customer';
			$authAssign->created_at=date('m/d/Y h:i:s');
			$authAssign->save(false);

		}



	}
