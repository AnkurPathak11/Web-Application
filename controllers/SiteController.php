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
use yii\data\Pagination;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

   
    public function actionIndex()
    {
        return $this->redirect(['/admin/redirectdash']);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    public function actionSearchresult()
    {
        return $this->render('searchresult');
    }
    public function actionAbout()
    {
        return $this->render('about');
    }
	
	
	public function actionContact(){
	$model = new ContactForm();
	 if ($model->load(Yii::$app->request->post())){
	$Email=Yii::$app->request->post('ContactForm')['email'];
	$model->contact($Email);
	Yii::$app->session->setFlash('success', 'Bedankt voor het contact met ons, we komen binnenkort terug.');
	return $this->redirect(['/site/success']);	
	}
	else{
		Yii::$app->session->setFlash('error', 'Controleer alstublieft uw mail ');
		return $this->render('contact',['model' => $model,]);	
	}
	}


	public function actionCaravan(){
		$model = new Clients();
		return $this->render('caravan', [
            'model' => $model,
        ]);
	}
	
	public function actionAuto(){

		$model = new Clients();
		return $this->render('auto', [
            'model' => $model,
        ]);
	}
	
	public function actionBike(){

		$model = new Clients();
		return $this->render('bike', [
            'model' => $model,
        ]);
	}
	
	public function actionOffer(){

		$model = new Clients();
		return $this->render('offertecaravan', [
            'model' => $model,
        ]);
	}

	public function actionOfferauto(){

		$model = new Clients();
		return $this->render('offerteauto', [
            'model' => $model,
        ]);
	}
	
	public function actionOfferbike(){

		$model = new Clients();
		return $this->render('offertebike', [
            'model' => $model,
        ]);
	}
	
	 public function actionUserlogin()
    {
       $model = new LoginForm();
		 return $this->render('loginform', [
            'model' => $model,
        ]);
    }


	public function actionSuccess(){
		return $this->render('success');
	}
	public function actionError(){
		return $this->render('error');
	}
	public function actionUnauthorised(){
		return $this->render('unauthorised');
	}
    public function actionPaymentCancel()
    {
        print_r($_REQUEST);die;
    }

    public function actionPaymentSuccess()
    {
		return $this->render('success');
    }

}
