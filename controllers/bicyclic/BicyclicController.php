<?php

namespace app\controllers\bicyclic;
use Yii;
use yii\filters\AccessControl;
use yii\db\Query;
use yii\helpers\Html;
use yii\web\Controller;
use app\models\bicyclic\BikeDetails;
use app\models\bicyclic\BikeRequest;
use app\models\Products;
use app\controllers\Config;
use app\controllers\helper\AdminUser;
use yii\data\ActiveDataProvider;
use yii\filters\VerbFilter;
use Yii\helpers\ArrayHelper;
use yii\grid\ActionColumn;
use app\models\CardoenLatestDocuments;
use app\models\Clients;
use  app\controllers\helper\AzureStorage;
use yii\helpers\Url;


class BicyclicController extends Controller
{
 public function behaviors()
 {
    return [
        'verbs' => [
            'class' => VerbFilter::className(),
            'actions' => [
                'delete' => ['POST'],
            ],
        ],
        'access' => [ 
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true, 
                    'actions' => ['dashboard', 'bicyclicdetails', 'policy-certificate-download','invoice-certificate-download','view-details','request-report-page','details-report-page','request-bicyclicdetails','save'], 
                    'roles' => ['admin','bicycle_backoffice_admin', 'bicycle_backoffice_user'],
                ],
                [
                    'allow' => true, 
                    'actions' => ['dashboard', 'bicyclicdetails', 'policy-certificate-download','invoice-certificate-download','view-details','request-report-page','details-report-page','request-bicyclicdetails','save'], 
                    'roles' => ['bicycle_backoffice_readonly'],
                ],
                
            ],
        ],
    ];
}
public function actionDashboard()
{

    $detailsModel = new BikeDetails();
    $requestModel = new BikeRequest();

    $clientModel = new Clients();

           // $query = BikeDetails::find()->joinWith('BikeRequest', true);
           //  print_r($query);
    $productId = 100;
    $product = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();   
    $productName = $product['ProductName'];
    $reportPath = $product['ReportPath'];

    $bikeRequestQuery = BikeRequest::find()
    ->innerJoin(['bikeDetails' => BikeDetails::tableName()],'BikeRequest.Id = bikeDetails.TransactionId');
    $bikeDetailsQuery = BikeDetails::find()->where(['Status' =>'Policy Issued']);

    $bikeRequest = new ActiveDataProvider([
        'query' =>$bikeRequestQuery,
        'pagination'=> [
           'pageSize' => 10
       ], 'sort' => [
        'defaultOrder' => [
            'CreatedDate' => SORT_DESC      
        ],
    ],
]);

    $bikeDetails = new ActiveDataProvider([
        'query' =>$bikeDetailsQuery,
        'pagination'=> [
           'pageSize' => 10
       ], 'sort' => [
        'defaultOrder' => [
            'CreatedDate' => SORT_DESC      
        ],
    ],
]);

    $requestorDate = array('attribute'=>'RequestorRequestDate','format'=>'text','label'=>'Datum');
    $requestorName = array('attribute'=>'RequestorName','format'=>'text', 'label'=>'Naam');
    $policyNumber = array('attribute'=>'PolicyNumber','format'=>'raw', 'label'=>'Polis nummer','contentOptions'=>['style'=> 'text-decoration-line: underline'],'value'=> function($detailsModel){
        return Html::a($detailsModel->PolicyNumber,Url::toRoute(['/bicyclic/bicyclic/bicyclicdetails','id'=>$detailsModel->Id]));
    });
    $requestorReferenceNumber = array('attribute'=>'RequestorReferenceNumber',
        'format'=>'text', 'label'=>'Referentie Nummer');
    $vehicleSerialNumber = array('attribute'=>'VehicleSerialNumber','format' => 'raw','label'=>'Vehicle Serial Number','contentOptions'=>['style'=> 'text-decoration-line: underline'],'value'=> function($requestModel){
        return Html::a($requestModel->VehicleSerialNumber,Url::toRoute(['/bicyclic/bicyclic/request-bicyclicdetails','id'=>$requestModel->Id]));
    });
    $status = array('attribute'=>'Status','format'=>'text','label'=>'Toestand');

    $purchaseDate = array('attribute' =>'PurchaseDate', 'format'=>'text', 'label'=>'Datum');

    $grossPremium = array('attribute' => 'GrossPremium', 'format' => 'text', 'label'=>'Premie');

    $vehicleModel = array('attribute' => 'VehicleModel', 'format' => 'text');


    $newCasesColData = array();
    $recentTransactionColData = array();
    $gridPanelArray = $this->gridViewPanel($requestorDate,$requestorName,$requestorReferenceNumber,$vehicleSerialNumber,$status,$purchaseDate,$grossPremium,$vehicleModel, $policyNumber);

    $newCasesColData = $gridPanelArray['newCasesColData'];
    $recentTransactionColData = $gridPanelArray['recentTransactionColData'];
    $searchModel = BikeRequest::find();
    $listData = ArrayHelper::map($searchModel, 'Id', 'PolicyHolderFirstName');

    if(Yii::$app->request->isPost){
        $requestModel->load(Yii::$app->request->post());
        $firstName = $requestModel->PolicyHolderFirstName;
        $lastName = $requestModel->PolicyHolderLastName;
        if($firstName != null || $lastName != null){



            $searchModel = BikeRequest::find()
            ->innerJoin(['bikeDetails' => BikeDetails::tableName()],'BikeRequest.Id = bikeDetails.TransactionId')
            ->Filterwhere(['like','BikeRequest.PolicyHolderFirstName', $firstName])->andFilterWhere(['like', 'BikeRequest.PolicyHolderLastName', $lastName])->asArray()->all();



            $listData = ArrayHelper::map($searchModel , 'Id', function($searchModel){
                return $searchModel['PolicyHolderFirstName'] . ' ' . $searchModel['PolicyHolderLastName'] . ' ' . $searchModel['RequestorEmail'];});


        }    
    }


    return $this->render('/bicyclic/bicyclicdashboard', ['detailsModel' => $detailsModel,'productId'=>$productId,'productName'=>$productName,'requestModel'=>$requestModel,'newCasesColData'=>$newCasesColData, 'recentTransactionColData'=>$recentTransactionColData,'newCasesDataProvider'=>$bikeRequest, 'recentTransactionDataProvider'=>$bikeDetails,'reportPath'=>$reportPath,
        'searchModel'=>$searchModel, 'listData'=>$listData ]);

}

public function gridViewPanel($requestorDate,$requestorName,$requestorReferenceNumber,$vehicleSerialNumber,$status,$purchaseDate,$grossPremium,$vehicleModel, $policyNumber)
{
  $newCasesColData =   array(
      $requestorDate,
      $requestorName,
      $requestorReferenceNumber,
      $vehicleSerialNumber,
      $status,

  );

  $recentTransactionColData = array(
    $purchaseDate,
    $policyNumber,
    $grossPremium,
    $vehicleModel,
    $status,
);

  return $gridViewArray = array('newCasesColData'=>$newCasesColData, 'recentTransactionColData' => $recentTransactionColData);
}

public function actionBicyclicdetails($id)
{

    $detailsModel = $this->findModel($id);
    $clientId = $detailsModel->ClientId ; 
    $clientModel = Clients::find()->where(['UserId'=>$clientId])->one();
    return $this->render('/bicyclic/bicyclicdetails',['detailsModel'=>$detailsModel, 'clientModel'=>$clientModel]);

}
public function actionRequestBicyclicdetails($id)
{

    $requestModel = $this->requestFindModel($id);
    $transactionId = $requestModel->Id;
    $detailsModel = BikeDetails::find()->where(['transactionId'=>$transactionId])->one();
    $clientId = $detailsModel->ClientId ; 
    $clientModel = Clients::find()->where(['UserId'=>$clientId])->one();
    return $this->render('/bicyclic/bicyclicdetails',['detailsModel'=>$detailsModel, 'clientModel'=>$clientModel]);

}


protected function findModel($id){
    if (($detailsModel = BikeDetails::findOne(['id' => $id])) !== null){

        return $detailsModel;
    }
    throw new NotFoundHttpException('The requested page does not exist.');

}

protected function requestFindModel($id){
    if (($requestModel = BikeRequest::findOne(['id' => $id])) !== null){

        return $requestModel;
    }
    throw new NotFoundHttpException('The requested page does not exist.');

}

public function actionViewDetails(){
    $id = Yii::$app->request->Post('BikeRequest')['Id'];
    $requestModel = BikeRequest::find()->where(['Id' => $id])->one();
    $detailsModel =  BikeDetails::find()->where(['transactionid' => $id])->one();
    if($detailsModel == null ){
        Yii::$app->session->setFlash('error','No Data Found');
        return $this->redirect(['/bicyclic/bicyclic/dashboard']); 

    }
    else{
     $clientId = $detailsModel['ClientId'];

     $clientModel =  Clients::find()->where(['UserId' => $clientId])->one();
     return $this->render('/bicyclic/bicyclicdetails',['detailsModel'=>$detailsModel, 'clientModel'=>$clientModel]);
 }
}

public function actionRequestReportPage(){
    $requestModel = new BikeRequest();
    $detailsModel = new BikeDetails();
    $productId = 100;
    $product = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();   
    $productName = $product['ProductName'];
    $bikeRequestQuery = BikeRequest::find()
    ->innerJoin(['bikeDetails' => BikeDetails::tableName()],'BikeRequest.Id = bikeDetails.TransactionId');

    $bikeRequest = new ActiveDataProvider([
        'query' =>$bikeRequestQuery,
        'pagination'=>[
            'pageSize' => 12
        ],
        'sort' => [
            'defaultOrder' => [
                'CreatedDate' => SORT_DESC      
            ],
        ],
    ]);

    return $this->render('/bicyclic/requestreport', ['productId'=>$productId,'productName'=>$productName,'requestModel'=>$requestModel, 'bikeRequest'=>$bikeRequest,'detailsModel'=> $detailsModel]);
}

public function actionSave(){
    $detailId = $_GET['detailId'];
    $userId = $_GET['UserId'];

}
public function actionDetailsReportPage(){
    $detailsModel = new BikeDetails();
    $productId = 100;
    $product = Products::find()->where('ProductId=:ProductId',[':ProductId'=>$productId])->one();   
    $productName = $product['ProductName'];
    $reportPath = $product['ReportPath'];

    $bikeDetailsQuery = BikeDetails::find()->where(['Status' =>'Policy Issued']);

    $bikeDetails = new ActiveDataProvider([
        'query' =>$bikeDetailsQuery,
        'pagination'=>[
            'pageSize' => 12
        ],
        'sort' => [
            'defaultOrder' => [
                'CreatedDate' => SORT_DESC      
            ],
        ],

    ]);

    return $this->render('/bicyclic/detailsreport', ['productId'=>$productId,'productName'=>$productName,'detailsModel'=>$detailsModel,'bikeDetails'=>$bikeDetails]);
}



public function actionPolicyCertificateDownload(){


    $id = $_GET['data'];
    // print_r(Yii::$app->request->url);exit;
    $bikeDetails = BikeDetails::find()->where(['Id' => $id])->one();

    if($bikeDetails != null){
        $productId = $bikeDetails['ProductId'];
        $offerId =$bikeDetails['Id'];
        $clientId =$bikeDetails['ClientId'];
        $productName = $bikeDetails['BikeDealer'];
    }
    else{
        Yii::$app
        ->session
        ->setFlash('error', "Data is not present");
        return $this->redirect(['bicyclic/bicyclic/dashboard']);
    }
    $cardoenLatestDocuments = CardoenLatestDocuments::find()->where(['ProductId' => $productId])->andWhere(['offerId' => $offerId])->one();
    $filePath = $cardoenLatestDocuments['DocFile9'];
    $fileNamePath = str_replace('\\', '/', $filePath);
    $fileName = pathinfo($fileNamePath, PATHINFO_BASENAME);

    if ($fileName == null || $fileName== ['bicyclic/bicyclic/dashboard'])
    {
        Yii::$app
        ->session
        ->setFlash('error', "Document is not present");
        return $this->redirect(['bicyclic/bicyclic/dashboard']);
    }
    $docType = $fileName;
    $file = AzureStorage::insureYourCargoAzureStorage($docType, $fileName, $productName);
    // error_log($file, 3, $logfile);
    return $file;

    // $file = fopen($filePath, "r");
    // $source = stream_get_contents($file);
    // header("Content-type: application/octet-stream");
    // header("Content-disposition: attachment;filename=" . $fileName);
    // fclose($file);
    // return $source;
}

public function actionInvoiceCertificateDownload(){

    $detailsModel = new BikeDetails();
    $requestModel = new BikeRequest();

    $id = $_GET['data'];
    // print_r(Yii::$app->request->url);exit;
    $bikeDetails = BikeDetails::find()->where(['Id' => $id])->one();

    if($bikeDetails != null){
        $productId = $bikeDetails['ProductId'];
        $offerId =$bikeDetails['Id'];
        $clientId =$bikeDetails['ClientId'];
        $productName = $bikeDetails['BikeDealer'];
    }
    else{
        Yii::$app
        ->session
        ->setFlash('error', "Data is not present");
        return $this->redirect(['/bicyclic/bicyclic/bicyclicdetails']);
    }
    $cardoenLatestDocuments = CardoenLatestDocuments::find()->where(['ProductId' => $productId])->andWhere(['offerId' => $offerId])->one();
    $filePath = $cardoenLatestDocuments['DocFile8'];
    $fileNamePath = str_replace('\\', '/', $filePath);
    $fileName = pathinfo($fileNamePath, PATHINFO_BASENAME);

    if ($fileName == null || $fileName== '')
    {
        Yii::$app
        ->session
        ->setFlash('error', "Document is not present");
        return $this->redirect(['/bicyclic/bicyclic/dashboard']);
    }
    $docType = $fileName;
    $file = AzureStorage::insureYourCargoAzureStorage($docType, $fileName, $productName);
    // error_log($file, 3, $logfile);
    return $file;

    // $file = fopen($filePath, "r");
    // $source = stream_get_contents($file);
    // header("Content-type: application/octet-stream");
    // header("Content-disposition: attachment;filename=" . $fileName);
    // fclose($file);
    // return $source;
}

}