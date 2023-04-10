<?php 

namespace app\controllers;
// require_once "/vendor/autoload.php";

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\Html;
use app\modules\cargoinsurance\helper\ConfigurationHelper;
use yii\helpers\FileHelper;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;
use app\models\Products;
use app\modules\cargoinsurance\models\CargoCustomers;
use app\modules\cargoinsurance\models\CargoMasterInsurers;
use app\models\Clients;
use app\models\cargo\PortalReports;
use app\controllers\helper\AzureStorage;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\File\FileRestProxy;
use app\models\cargo\CargoMasterInsurersSearch;
use app\models\cargo\CargoCustomersSearch;


Class ReportsController extends Controller
{
  public function behaviors()
  {
    return[ 
      'access'=>[
        'class'=>AccessControl::class,
        'rules'=> [
          [
            'allow'=>true,


            'actions'=>['report-panel','get-report','get-yearly-report','insurer-report-panel'],
            'roles' => ['cargo_backoffice_admin','cargo_supervisor'],
          ]
        ]
      ]
    ];
  }



  public function actionReportPanel()
  {

    $customerSearchModel = new CargoCustomersSearch();
    $customerDataProvider =  $customerSearchModel->search(Yii::$app->request->queryParams);
    $concordiaArray = ['data'];

    $concordiaDataProvider = new ArrayDataProvider([
      'allModels'=>$concordiaArray
    ]);

    $year = 2022;
    $yearArr[] = null;
    $monthNameArr = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];
    for($i=0;$i<=5;$i++)
    {
      $yearArr[$i] = $year+$i;
    }
   
    return $this->render('/insure_your_cargo/cargoreports.php',
     ['customerDataProvider'=>$customerDataProvider,'monthNameArr'=>$monthNameArr,'yearArr'=>$yearArr,'concordiaDataProvider'=>$concordiaDataProvider,'customerSearchModel'=>$customerSearchModel]);     
  }
   public function actionInsurerReportPanel(){
   $insurerSearchModel = new CargoMasterInsurersSearch();
   $insurerDataProvider =  $insurerSearchModel->search(Yii::$app->request->queryParams);
   $concordiaArray = ['data'];
   
   $concordiaDataProvider = new ArrayDataProvider([
    'allModels'=>$concordiaArray
  ]);
   

   return $this->render('/insure_your_cargo/cargoinsurereport.php',
     ['insurerDataProvider'=>$insurerDataProvider,'concordiaDataProvider'=>$concordiaDataProvider,'insurerSearchModel'=>$insurerSearchModel]); 
 }

  public function actionGetReport()
  {



$fileName = $_GET['data']['fileName'];
$filePath =  $_GET['data']['filePath'];


$docType = $fileName;



$file =AzureStorage::insureYourCargoAzureStorage($docType, $fileName, $filePath);
return $file;


}
public function actionGetYearlyReport(){
  $type = $_GET['data']['type'];
  $year = $_GET['data']['year'];

  $portalReports = PortalReports::find()->where(['Type'=>$type])->andWhere(['YEAR(ReportingDate)'=>$year])->orderBy(['Id'=>SORT_DESC])->one();
  $fileName = $portalReports['FileName'];
  $filePath = $portalReports['CloudStorageDir'];
  $docType = $fileName;
  $file =AzureStorage::insureYourCargoAzureStorage($docType, $fileName, $filePath);
  return $file;

}

public function logMessage($obj)
{
  $log_file = "c://inetpub//wwwroot//admin//runtime//logs//report.log";
  $log_message = print_r($obj, true);
  error_log($log_message, 3, $log_file);        
}



}

?>
