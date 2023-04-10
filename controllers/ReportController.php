<?php 

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\Html;
use app\modules\cargoinsurance\helper\ConfigurationHelper;
use yii\helpers\FileHelper;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use yii\base\DynamicModel;
use yii\helpers\Url;
use app\models\cargo\CargoRequest;
use app\models\cargo\CargoDetails;
use app\models\Products;
use app\modules\cargoinsurance\models\CargoCustomers;
use app\models\Clients;
use app\modules\cargoinsurance\models\CargoRequestVirtualModel;


Class ReportController extends Controller
{
  public function behaviors()
  {
    return[ 
      'access'=>[
        'class'=>AccessControl::class,
        'rules'=> [
          [
                    'allow'=>true,
                    'actions'=>['report-panel','view-all'],
                    'roles' => ['cargo_backoffice_admin','cargo-recent-request'],
                    ]
        ]
      ]
    ];
  }



	public function actionReportPanel()
	{ 

    if(isset($_GET['data']))
    {
      $data = $_GET['data'];
    }else
    {
      $data =0;
    }

    $newCasesQuery = CargoRequestVirtualModel::find()
        ->leftJoin(['c_details'=>CargoDetails::tableName()],'CargoRequest.ID = c_details.Transaction_id')
        ->innerJoin(['products'=>Products::tableName()],'CargoRequest.ProductId = products.ProductId')
        ->leftJoin(['clients'=>Clients::tableName()],'CargoRequest.CreatedBy = clients.UserId')
        ->where('CargoRequest.ContractId is not null')
        ->andWhere('CargoRequest.ProductId > 16')
        ->andWhere("c_details.Status = 'final'")
        ->select(['CargoRequest.*','c_details.CertificateNumber as CertificateNumber','products.DisplayName as CustomerName']);

       $newCasesDataProvider = $this->getDataProvider($newCasesQuery); 

       $recentTransactionDataProvider = $this->getDataProvider($newCasesQuery);


       $gridViewArray = $this->getDataCols();

       $newCasesColData = $gridViewArray['newCasesColData'];

       $recentTransactionColData = $gridViewArray['recentTransactionColData'];

      if($data == 1)
      {
        $col = 1;  
        $newCasesDataProvider->setPagination(['pageSize'=>false]);
          return $this->render('/insure_your_cargo/cargorecentrequestpanel.php',['productId'=>null,'productName'=>null, 'newCasesDataProvider'=>$newCasesDataProvider,'newCasesColData'=>$newCasesColData,'col'=>$col]);
      }
      elseif ($data == 2)
      {
        $col =1;
        $recentTransactionDataProvider->setPagination(['pageSize'=>false]);
          return $this->render('/insure_your_cargo/cargorecentrequestpanel.php',['recentTransactionDataProvider'=>$recentTransactionDataProvider,'recentTransactionColData'=>$recentTransactionColData,'col'=>$col]);
      }
      else
       {
        $reportsFolderPath = 'reports\insure_your_cargo';
        $reportsArr = $this->getReports($reportsFolderPath);
       
        $reportPanelDetails = new ArrayDataProvider([
          'allModels' => $reportsArr]);
      
		  return $this->render('/insure_your_cargo/cargoreports.php',
			['reportPanelDetails'=>$reportPanelDetails, 'newCasesDataProvider'=>$newCasesDataProvider,'recentTransactionDataProvider'=>$recentTransactionDataProvider,'newCasesColData'=>$newCasesColData,'recentTransactionColData'=>$recentTransactionColData]);
      }



  }

  public function getDataProvider($query)
  {
       $DataProvider = new ActiveDataProvider([
        'query'=> $query,
        'pagination'=>['pageSize'=>10],
         'sort' => [
                'defaultOrder' => [
                    'CreatedDate' => SORT_DESC,
                ]
            ]
       ]);

       $DataProvider->sort->attributes['CreatedDate'] = [
          'desc'=>['c_details.CreatedDate'=>SORT_DESC],
          'asc'=>['c_details.CreatedDate'=>SORT_ASC]
       ];

       return $DataProvider;

  }

  public function getDataCols()
  {

       $dateValue = function($data)
       {
          $date = date_create($data->CreatedDate);
          return date_format($date, 'd/m/y');
       };

       $customer = array('attribute'=>'CustomerName','label'=>'Customer');

       $createdDate = array('attribute'=>'CreatedDate','label'=>'Date','value'=>$dateValue);



      $newCasesColData = array();
       $recentTransactionColData = array();

       $gridViewArray = $this->gridViewPanel($createdDate,$customer);

       return  $gridViewArray;
  }

  public function gridViewPanel($createdDate,$customer)
  {
    $newCasesColData = array(
      $createdDate,
      $customer,

    );

    $recentTransactionColData = array(
      $createdDate,
      $customer,

    );

    return $gridViewArray = array('newCasesColData'=> $newCasesColData,'recentTransactionColData'=>$recentTransactionColData); 
  }



  function getReports($reportsFolderPath)
  {
        $files=FileHelper::findFiles($reportsFolderPath);
        $reportsArr = array();
        $files = array_diff($files, array('.', '..'));
        sort($files);

        foreach($files as $file)
        { 
            $fileName = substr($file,strrpos($file,'\\'));
            $fileName = str_replace('\\','',$fileName);

             $model = new DynamicModel(['name', 'url']);
            $model->name = $fileName;
            $model->url = Url::base(true).'\\'.$file;
            array_push($reportsArr,$model);        
        }
        return $reportsArr;
  }

  

}

?>
