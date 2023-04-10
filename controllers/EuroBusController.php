<?php

namespace app\controllers;

use Yii;
use yii\base\Exception;
use app\models\Clients;
use app\base\Model;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\Html ;
use app\controllers\helper\RestAPIController;
use app\controllers\Config;
use app\controllers\helper\LogManager; 
use app\searchmodels\eurobus\DamageRequestSearch;
use app\searchmodels\eurobus\BusRequestSearch;
use app\models\eurobus\DamageRequest;
use app\models\eurobus\BusRequest;
use app\controllers\helper\AzureStorage;
use app\controllers\helper\Utils ;
use app\controllers\helper\GridColumns ;
use app\controllers\helper\Messages;
use app\controllers\helper\EmailService;
use yii\data\ArrayDataProvider;
use app\searchmodels\eurobus\EuroBusRequestMonitorSearch;


class EuroBusController extends Controller
{

    public function behaviors(){
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['dashboard','download-file','replay','requests-view-all','view-score-grid','email'],
                'rules' => [
                    [

                        'actions' => ['dashboard','download-file','replay','requests-view-all','view-score-grid','email'],
                        'allow' => true,
                        'roles' => ['euro_bus_backoffice_admin','admin'],
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

    public function actionDashboard()
    {
        $damageSearchDataProvider = $this->getSearchModelDataProvider(DamageRequestSearch::class);
        $busSearchDataProvider = $this->getSearchModelDataProvider(BusRequestSearch::class);
        $busRequestTodayScore = $this->getTodayScoreCount(BusRequest::class) ;
        $damageRequestTodayScore = $this->getTodayScoreCount(DamageRequest::class);
        
        $statusDropDown = array_merge(Yii::$app->params['euroBusSuccessStatus'],Yii::$app->params['euroBusFailureStatus']);
        $layout = '{items}';
        $busRequestColumns = $this->getRequestColumns(true,true,'bus') ;
        $damageRequestColumns = $this->getRequestColumns(true,true,'damage');

        return $this->render('index',[
            'damageRequestSearchModel' => $damageSearchDataProvider['searchModel'] ,
            'damageRequestDataProvider' => $damageSearchDataProvider['dataProvider'],
            'busRequestSearchModel' => $busSearchDataProvider['searchModel'],
            'busRequestDataProvider' => $busSearchDataProvider['dataProvider'],
            'statusDropDown' => $statusDropDown,
            'layout'=>$layout,
            'busRequestColumns'=>$busRequestColumns,
            'damageRequestColumns'=>$damageRequestColumns,
            'EBTOCCBusRequestTodayScore' => $busRequestTodayScore['EBTOCCTodayScore'],
            'EBTOCCDamageRequestTodayScore' => $damageRequestTodayScore['EBTOCCTodayScore'],
            'CCTOEBBusRequestTodayScore' => $busRequestTodayScore['CCTOEBTodayScore'],
            'CCTOEBDamageRequestTodayScore' => $damageRequestTodayScore['CCTOEBTodayScore']

        ]);
    }

    public function actionDownloadFile($id,$requestType,$fileType,$fileName)
    {
        $request = null ;
        $fileShare = Yii::$app->params['fileShare'];
        $euroBusProductName =  Yii::$app->params['euroBusProductName'];
        $utils = new Utils() ;
        $requestModelClass = $requestType == 'damage' ? DamageRequest::class : BusRequest::class ;
        $requestModel = $this->findModel($requestModelClass);
        $request = $requestModel->where(['Id'=>$id])->one() ;
        $productName = $this->getProductFileName($fileType,$request);
        
        if ($fileName != null || $request != null || $fileType != null )
        {
            return $this->downloadFileType($fileName,$fileShare,$euroBusProductName.$productName,$fileType);
        }

        Yii::$app->session->setFlash('error', "Document is not present");
        return $this->redirect(['euro-bus/dashboard']);
    }

    private function getTodayScoreCount($modelClass)
    {
        $utils = new Utils() ;
        $modelFind = $modelClass::find() ;
        $EBTOCCTodayScore = $this->getRequestTypeTodayScore($modelFind,$utils->getParams('euroBusEBTOCC'));
        $CCTOEBTodayScore = $this->getRequestTypeTodayScore($modelFind,$utils->getParams('euroBusCCTOEB'));

        return [
            'EBTOCCTodayScore'=>$EBTOCCTodayScore,
            'CCTOEBTodayScore'=>$CCTOEBTodayScore
        ] ;
    }

    public function actionReplay()
    {
        $sourceFileName = Yii::$app->request->get('sourceFileName') == null ? null  : Yii::$app->request->get('sourceFileName')  ;
        $utils = new Utils();
        if(Yii::$app->request->post() || $sourceFileName != null )
        {
            $data = null ;
            $url = null ;
            if($sourceFileName == null)
            {
                $post = Yii::$app->request->post() ;
                 $data = array('transformationType'=>$post['transformationType'],'requestType'=>$post['requestType'],'startDate'=>date('Y-m-d H:i',strtotime($post['startDate'])),'endDate'=>date('Y-m-d H:i',strtotime($post['endDate'])));
                 $url =  Yii::$app->params['euroAPIBaseUrl'].Yii::$app->params['euroBusAPIReplay'];
            }else
            {
                
                $id = Yii::$app->request->get('id');
                $transformationType = Yii::$app->request->get('transformationType') ;  
                $requestType = Yii::$app->request->get('requestType') ; 
                $destFileNameWithPath = $utils->getParams('euroBusReplayPath');

                $url =  Yii::$app->params['euroAPIBaseUrl'].Yii::$app->params['euroBusAPIReplaySingleFile'];
                $data = array('sourceFileName'=>$sourceFileName,'transformationType'=>$transformationType) ;

            }
            
            $authorization = 'Basic '. base64_encode(Yii::$app->params['euroBusAPIAuthorization']);    
            $this->sendAPIRequest($data, $url, $authorization) ;

            return $this->redirect('dashboard');
        }
        $transformationType = $utils->getParams('euroBusTransformationType') ;
        return $this->render('replay',[
            'transformationType'=>$transformationType
        ]) ;
    }

    public function sendAPIRequest($data, $url, $authorization)
    {
         $newdata = json_encode($data);
            $response =  RestAPIController::invokePostApi($url, $authorization, $newdata);

            LogManager::logMessage($response);
            $responseStatus = isset($response->status)  ?  $response->status : $response  ; 
            $responseStatus = strtolower($responseStatus) ;
            if($responseStatus == 'success' )
            {
               Yii::$app->session->setFlash('success','Request for reprocessing has been raised successfully.');
            }
            else{
                Yii::$app->session->setFlash('error','An error occurred while submitting the request. Please retry.');
            }
            return $response ; 
    }


    public function actionRequestsViewAll($requestType){

        $requestSearchModel = $requestType == 'damage' ? DamageRequestSearch::class : BusRequestSearch::class ;
        $requestColumns = $this->getRequestColumns(true,true,$requestType);
        $requestGridName = $requestType == 'damage' ? 'Claims Request' : 'Production Request' ;
        $requestSearchDataProvider = $this->getSearchModelDataProvider($requestSearchModel);
        $statusDropDown = array_merge(Yii::$app->params['euroBusSuccessStatus'],Yii::$app->params['euroBusFailureStatus']);
        $layout= '{items}{pager}';

        return $this->render('requestviewall',[
        'requestSearchModel' => $requestSearchDataProvider['searchModel'],
        'requestDataProvider' => $requestSearchDataProvider['dataProvider'],
        'statusDropDown' => $statusDropDown,
        'layout'=>$layout,
        'requestColumns'=>$requestColumns,
        'requestGridName'=>$requestGridName,
        'requestType' => $requestType

        ]);
    }

    public function downloadFileType($fileName,$fileShare,$euroBusProductName,$fileType)
    {   
        $utils = new Utils();
        $downloadSuccess = false ;
        $filePath = $utils->getParams('euroBusLocalOutputPath');
        if($fileType == 'import')
        {
            $downloadSuccess = $utils->isFileExists($filePath,$fileName); 
            if($downloadSuccess)
            {
                $filePath = $utils->getParams('euroBusLocalOutputPath');
                return $utils->downloadFileFromLocal($filePath,$fileName);
            }
        }

        
        return $utils->downloadFileFromAzure($fileName,$productName,$fileShare);
    }

    public function findModel($modelClass)
    {
        return $modelClass::find() ;
    }

    public function actionExcelExport($requestType)
    {
        $requestModelSearchClass = $requestType == 'damage' ? DamageRequestSearch::class : BusRequestSearch::class ;
        $requestSearchDataProvider = $this->getSearchModelDataProvider($requestModelSearchClass);
        $dataProvider = $requestSearchDataProvider['dataProvider'];
        $dataProvider->setPagination(['pageSize' => false]);
        $requestGridColumns = $this->getRequestColumns(false,false,$requestType) ;
        $fileName = $requestType == 'damage' ? Messages::CLAIMS_REPORT : Messages::PRODUCTION_REPORT ;
        $excelName = $fileName . date('Ymd').Messages::EXCEL_EXT;
        return $this->render('phpexcel', [
            'searchModel' => $requestSearchDataProvider['searchModel'] ,
             'dataProvider' => $dataProvider,
              'excelName' => $excelName,
              'requestGridColumns'=>$requestGridColumns
            ]);
    }

    public function getSearchModelDataProvider($modelSearchClass)
    {
        $modelSearchModel = new $modelSearchClass;
        $modelDataProvider = $modelSearchModel->search(isset(Yii::$app->request->queryParams[1]) == null ? Yii::$app->request->queryParams : Yii::$app->request->queryParams[1]);
        return ['searchModel'=> $modelSearchModel, 'dataProvider'=> $modelDataProvider] ;
    }

    public function getRequestColumns($actionButton,$filter,$requestType)
    {
        $gridColumns = new GridColumns() ;
        $utils = new Utils() ;
        $value = $gridColumns->getColumnValue();
        $format = 'text';
        $buttons = $this->getRequestButton() ;
        $filterOptions = [] ;
        $statusFilterDropdown = array_merge(Yii::$app->params['euroBusSuccessStatus'],Yii::$app->params['euroBusFailureStatus']);
        $attachmentCountVal = $this->getFileAttachmentCount() ;
        $sourceFileNameVal = function ($model)
        {
            return $this->modifySourceColumnValue($model['SourceFileName'],$model['ExportFileName'],$model['RequestType']) ;
        }  ;
        $requestValue = $this->getRequestColumnValue();
        $requestValueFilterDropdown = $utils->getParams('euroBusTransformationType');
        $additionalParams = [] ;
       
        $attachmentFileName = function ($model)
        {
            $sourceFileNameVal = $this->modifySourceColumnValue($model['SourceFileName'],$model['ExportFileName'],$model['RequestType']) ;
            $file  = $this->getFileAttachment($sourceFileNameVal,$model['RequestType']);
            if(isset($file) ){
                return  $this->getFileAttachmentName($file,$sourceFileNameVal) ;  
            }
        } ; 
        
        $attachmentFile = $gridColumns->addGridColumn('SourceFileName','Attachment Files',['style'=>'width: 20%'],$attachmentFileName,$format,$filter,$filterOptions,['visible'=>!$filter]);
        

        if($requestType == 'damage')
        {

            $CCClaimsReferenceValue = function($model){ if(preg_match('/[a-z\-0-9]/i', $model['CCClaimsReference'])) { return $model['CCClaimsReference'] ;} };
            $EBClaimReference = $gridColumns->addGridColumn('EBClaimReference','EBClaimReference',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $CCClaimsReference = $gridColumns->addGridColumn('CCClaimsReference','CCClaimsReference',['style'=>'width: 10%'],$CCClaimsReferenceValue,$format,$filter,$filterOptions,$additionalParams);
            $licencePlate = $gridColumns->addGridColumn('LicensePlate','Licence Plate',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $sourceFileName = $gridColumns->addGridColumn('SourceFileName','SourceFile Name',['style'=>'width: 35%'],$sourceFileNameVal,$format,$filter,$filterOptions,$additionalParams);
            $createdDate = $gridColumns->addGridColumn('CreatedDate','Created Date',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $status = $gridColumns->addGridColumn('Status','Status',['style'=>'width: 25%'],$value,$format,$statusFilterDropdown,$filterOptions,$additionalParams);
            $createdDate = $gridColumns->addGridColumn('CreatedDate','Created Date',['style'=>'width: 10%'],$value,['date', 'php:d-m-Y H:i'],$filter,['class'=>'form-control','type'=>'date'],$additionalParams );
            $gridButtons = $gridColumns->addActionColumn('{download}{replay}','Action',['style'=>'width: 10%','class'=>'text-center'],$buttons) ;
            $attachmentCount = $gridColumns->addGridColumn('SourceFileName','Attachment Count',['style'=>'width:2%; white-space: break-spaces;'],$attachmentCountVal,$format,false,$filterOptions,$additionalParams);
            $requestType = $gridColumns->addGridColumn('RequestType','Request Type',['style'=>'width: 10%'],$requestValue,$format,$requestValueFilterDropdown,$filterOptions,$additionalParams);

            $renderGridColumns = [$EBClaimReference,$licencePlate,$CCClaimsReference,$createdDate,$sourceFileName,$requestType,$status,$attachmentCount,$attachmentFile] ;
    
        }else if($requestType == 'bus')
        {
            
            $chassisNumber = $gridColumns->addGridColumn('ChassisNumber','Chassis Number',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $licencePlate = $gridColumns->addGridColumn('LicencePlate','Licence Plate',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $sourceFileName = $gridColumns->addGridColumn('SourceFileName','SourceFile Name',['style'=>'width: 35%'],$sourceFileNameVal,$format,$filter,$filterOptions,$additionalParams);
            $createdDate = $gridColumns->addGridColumn('CreatedDate','Created Date',['style'=>'width: 10%'],$value,['date', 'php:d-m-Y H:i'],$filter,['class'=>'form-control','type'=>'date'],$additionalParams );
            $status = $gridColumns->addGridColumn('Status','Status',['style'=>'width: 25%'],$value,$format,$statusFilterDropdown,$filterOptions,$additionalParams);
            $gridButtons = $gridColumns->addActionColumn('{download}{replay}','Action',['style'=>'width: 10%','class'=>'text-center'],$buttons) ;  
            $attachmentCount = $gridColumns->addGridColumn('SourceFileName','Attachment Count',['style'=>'width:2%; white-space: break-spaces;'],$attachmentCountVal,$format,false,$filterOptions,$additionalParams);
            $requestType = $gridColumns->addGridColumn('RequestType','Request Type',['style'=>'width: 10%'],$requestValue,$format,$requestValueFilterDropdown,$filterOptions,$additionalParams);
    
            $renderGridColumns = [$chassisNumber,$licencePlate,$createdDate,$sourceFileName,$requestType,$status,$attachmentCount,$attachmentFile] ;
        }else if($requestType == 'monitor')
        {
            $reportedDate = $gridColumns->addGridColumn('ReportingDatetime','Date',['style'=>'width: 10%'],$value,['date', 'php:d-m-Y '],$filter,['class'=>'form-control','type'=>'date'],$additionalParams );
            $requestTypeCol = $gridColumns->addGridColumn('RequestType','Request Type',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $totalProdRequest = $gridColumns->addGridColumn('TotalProdRequest','Total Prod. Request',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $successProdRequest = $gridColumns->addGridColumn('SuccessProdRequest','Success Prod. Request',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $failureProdRequest = $gridColumns->addGridColumn('FailedProdRequest','Failure Prod. Request',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $totalClaimRequest = $gridColumns->addGridColumn('TotalClaimRequest','Total Claim. Request',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $successClaimRequest = $gridColumns->addGridColumn('SuccessClaimRequest','Success Claim Request',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $failureClaimRequest = $gridColumns->addGridColumn('FailedClaimRequest','Failure Claim Request',['style'=>'width: 10%'],$value,$format,$filter,$filterOptions,$additionalParams);
            $renderGridColumns = [$reportedDate,$requestTypeCol,$totalProdRequest,$successProdRequest,$failureProdRequest,$totalClaimRequest,$successClaimRequest, $failureClaimRequest ];
        }
        else 
        {
            $renderGridColumns = [];
        }

        if($actionButton)
        {
            array_push($renderGridColumns,$gridButtons);
        }

        
        return $renderGridColumns ;
    }

    public function getRequestButton()
    {
        $gridButton =[
                'download'=>function($url,$model)
                {
                    $className =  $model::className();
                    $classPos = strrpos($className,"\\");
                    $class = substr($className,$classPos+1);
                    $requestType = 'bus' ;
                    if($class == 'DamageRequest')
                    {
                        $requestType = 'damage' ;
                    }

                    $sourceFileNameVal =  $this->modifySourceColumnValue($model['SourceFileName'],$model['ExportFileName'],$model['RequestType']) ;
                    $brioImportFileNameVal = $this->modifySourceColumnValue($model['BrioImportFileName'],$model['OutputFileName'],$model['RequestType']) ;

                    $gridColumns = new GridColumns() ;
                    $sourceInputFile = $gridColumns->getDropdownItem($sourceFileNameVal,'Source Input File',['download-file','id'=>$model['Id'],'requestType' => $requestType,'fileType' => 'input','fileName'=>$sourceFileNameVal],'source input') ;
                    $brioImportFile = $gridColumns->getDropdownItem($brioImportFileNameVal,'Brio Import File',['download-file','id'=>$model['Id'],'requestType' => $requestType,'fileType' => 'import','fileName'=>$brioImportFileNameVal],'brio import') ;
                    $exportOutputFile =$gridColumns->getDropdownItem($model['OutputFileName'],'Export Output File',['download-file','id'=>$model['Id'],'requestType' => $requestType,'fileType' => 'export','fileName'=>$model['OutputFileName']],'export output') ; 

                    $azureStorage = new AzureStorage() ;
                    $utils = new Utils ;
                    $euroBusProductName = $utils->getparams('euroBusProductName') ;
                    $filePath = $euroBusProductName.$this->modifySourceColumnValue($utils->getparams('euroBusServerInput'),$utils->getparams('euroBusExportOuput'),$model['RequestType']) ; 
                    $sourceFileName = substr($sourceFileNameVal, 0, strpos($sourceFileNameVal, '_'));
                    $files = $azureStorage->getAzureFileList($filePath,$sourceFileName) ;
                    $filesCount = count($files);

                    $itemsList = [$sourceInputFile,$brioImportFile, $exportOutputFile];
                    $count = 1 ;
                    for($i = 0 ; $i<$filesCount ; $i++ )
                    {  
                        $fileName = 'Attachment '.$count  ;
                        $attachment = $gridColumns->getDropdownItem($files[$i]->getName(),$fileName,['download-file','id'=>$model['Id'],'requestType' => $requestType,'fileType' => 'attachment','fileName'=>$files[$i]->getName()],$fileName) ;
                        if($sourceFileNameVal != $files[$i]->getName() ){
                            array_push($itemsList,$attachment);  
                            $count++ ;   
                        }   
                    }

                    if($sourceFileNameVal != null && $sourceFileNameVal != '' )  {  
                        
                        return $gridColumns->getDropdownDownloadList($itemsList) ;
                    }
                    },  
            'replay' => function($url,$model)
            {
                $className =  $model::className();
                $classPos = strrpos($className,"\\");
                $class = substr($className,$classPos+1);
                $requestType = 'bus' ;
                if($class == 'DamageRequest')
                {
                    $requestType = 'damage' ;
                }

                return Html::a('<span class="fas fa-duotone fa-play text-center pr-2"></span>',                    [
                            'replay',
                            'id'=>$model['Id'],
                            'requestType' => $requestType,
                            'transformationType' => $model['RequestType'],
                            'sourceFileName'=>$model['SourceFileName'],
                        ],['title'=>'replay',
                        'data' => [
                            'confirm' => 'This action will reprocess the file. Are you sure?',
                            'method' => 'post',
                        ]
                        ]
            );
            },
            'email' => $this->getEmailButton() 
        ];

        return $gridButton ;
    }

    public function getProductFileName($fileType,$request)
    {
    $utils = new Utils() ;
    switch ($fileType) {
        case "input":
                $EBTOCCInput = $utils->getParams('euroBusServerInput');
                $CCTOEBInput  = $utils->getParams('euroBusExportOuput').$request['ExportFolder'] ;;
                $productName = $this->modifySourceColumnValue($EBTOCCInput,$CCTOEBInput,$request['RequestType']) ;
            break;
        case "import":
            $EBTOCCImport = $utils->getParams('euroBusBrioImport');
            $CCTOEBImport  = $utils->getParams('euroBusExportOuput').$request['ExportFolder'] ;;
            $productName = $this->modifySourceColumnValue($EBTOCCImport,$CCTOEBImport,$request['RequestType']) ;
            break;
        case "export":
            $subFolderName = $request['ExportFolder'] ;
            $productName = $utils->getParams('euroBusExportOuput').$subFolderName;
            break;
        case "attachment":
            $productName = $utils->getParams('euroBusServerInput');
            break;
        default:
            return null;
        }
        return $productName ;
    }

    public function getRequestTypeTodayScore($modelFind, $requestType)
    {
        $failureStatus = Yii::$app->params['euroBusFailureStatus'];
        $modelCurrentDate = $modelFind->where(['RequestType'=>$requestType])->andWhere(['like','CONVERT(date,CreatedDate)',date('Y-m-d')]);
        $modelCurrentDateCount  = $modelCurrentDate->count() ;
        $modelLastQuarterCount = $modelCurrentDate->andWhere(['>','CreatedDate', date('Y-m-d H:i:s', strtotime('-4 hour')) ])->count();
        $modelCurrentDateFailureCount = $modelFind->where(['RequestType'=>$requestType])->andWhere(['like','CONVERT(date,CreatedDate)',date('Y-m-d')])->andWhere(['in','Status',$failureStatus])->count() ;


        return ['modelCurrentDateCount'=>$modelCurrentDateCount,'modelCurrentDateFailureCount'=>$modelCurrentDateFailureCount,
        'modelLastQuarterCount'=>$modelLastQuarterCount
        ] ;
    }

    public function getFileAttachmentCount()
    {
        $attachmentCountVal = function($model){
            $sourceFileNameVal =  $this->modifySourceColumnValue($model['SourceFileName'],$model['ExportFileName'],$model['RequestType']) ;
            if($sourceFileNameVal != '' && $sourceFileNameVal != null ){
             $file = $this->getFileAttachment($sourceFileNameVal,$model['RequestType']) ;
             $fileCount =  count($file) == 0 ? 0 : count($file)-1;
             return $fileCount ;
            }
        };

        return $attachmentCountVal ;
    }

    public function modifySourceColumnValue($EBTOCCFileName,$CCTOEBFileName,$requestType)
    {
        $utils = new Utils() ;
        if($requestType == $utils->getParams('euroBusEBTOCC'))
        {
            return $EBTOCCFileName ;
        }
        else if($requestType == $utils->getParams('euroBusCCTOEB'))
        {
            return $CCTOEBFileName ;
        }
        else {
            return '';
        }
    }

    private function getRequestColumnValue()
    {
        $model = function($model)
        {
            $utils = new Utils();
            if($model['RequestType'] != '' && $model['RequestType'] != null ){
                $requestType = $utils->getParams('euroBusTransformationType')[$model['RequestType']];
                return $requestType ;
            }
        };

        return $model ;
    }

    public function getEmailButton()
    {
        $email = function($url,$model)
        {
            $requestType = $this->getRequestType($model) ;

            return Html::a('<span class="fas fa-envelope text-center pr-2"></span>',                    [
                'email',
                'id'=>$model['Id'],
                'requestType'=>$requestType,
            ],['title'=>'email',
            'data' => [
                'confirm' => 'This action will send mail to concordia. Are you sure?',
                'method' => 'post',
             ]
            ]
                );
        } ;

        return $email;
    }

    public function actionEmail($id,$requestType)
    {
        $emailService = new EmailService();
        $utils = new Utils();
        $fromEmail = $utils->getParams('euroBusFromEmail') ;
        $Email = $utils->getParams('euroBusToEmail') ;
        $busRequest = '';
        $damageRequest = '' ;

        if($requestType == 'bus'){
            $busRequest = BusRequest::find()->where(['Id'=>$id])->one() ;
            $templateId = $utils->getParams('euroBusProdTemplateId') ;
            $requestReason = $busRequest->RequestReason;
            $customerNumberCC = $busRequest->CustomerNumberCC ;
            $chassis = $busRequest->ChassisNumber ;
            $numberPlate = $busRequest->LicencePlate ;

            $emailVariables = array('field1'=>$requestReason,'field2'=>$customerNumberCC,'field4'=>$chassis,'field5'=>$numberPlate) ;
        }else
        {
            $damageRequest  = DamageRequest::find()->where(['Id'=>$id])->one() ;
            $templateId = $utils->getParams('euroBusClaimTemplateId') ; 

            $referentie  = $damageRequest->CCClaimsReference ;
            $date  = $damageRequest->AccidentDate ;
            $filiaal  = $damageRequest->CCBranchNumber ;
            $numberplate  = $damageRequest->LicensePlate;
            $expert  = $damageRequest->ExpertName ;
            $plaats  = $damageRequest->VehicleVisiblePlace ;
            $status  = $damageRequest->ExpertStatus ;
            $EBHwachtstatus  = $damageRequest->FileStatus  ;

            $emailVariables = array('field2'=>$referentie,'field4'=>$date,'field6'=>$filiaal,'field9'=>$numberplate,'field43'=>$expert,'field45'=>$plaats,'field51'=>$status,'field29'=>$EBHwachtstatus) ;
        }
       
        $response = $emailService->sendEmailWithTemplate($fromEmail, $Email, null , null, $templateId, $emailVariables);

        if($response->success())
        {
            Yii::$app->session->setFlash('success','Mail sent') ;
           
        }else
        {
            Yii::$app->session->setFlash('error','Mail sending Failed') ;
        }
        return $this->redirect('euro-bus/dashboard');
    }

    public function getRequestType($model)
    {
        $className =  $model::className();
        $classPos = strrpos($className,"\\");
        $class = substr($className,$classPos+1);
        $requestType = 'bus' ;
        if($class == 'DamageRequest')
        {
            $requestType = 'damage' ;
        }

        return $requestType ;
    }

    public function actionViewScoreGrid()
    {
        $utils = new Utils();
        $EBTOCCsearchDataprovider = $this->getSearchModelDataProvider(EuroBusRequestMonitorSearch::class);
        $CCTOEBsearchDataprovider = $this->getSearchModelDataProvider(EuroBusRequestMonitorSearch::class);
        $columns = $this->getRequestColumns(false,false,'monitor') ;
        $EBTOCCdataProvider = $EBTOCCsearchDataprovider['dataProvider'];
        $EBTOCCdataProvider->query->andWhere(['RequestType'=>$utils->getParams('euroBusEBTOCC')]);
        $CCTOEBdataProvider = $CCTOEBsearchDataprovider['dataProvider'];
        $CCTOEBdataProvider->query->andWhere(['RequestType'=>$utils->getParams('euroBusCCTOEB')]);
        return $this->render('scoregrid',[
            'EBTOCCdataProvider'=> $EBTOCCdataProvider,
            'CCTOEBdataProvider'=>$CCTOEBdataProvider,
            'columns'=>$columns
        ]);
    }

    public function getFileAttachment($sourceFileNameVal,$requestType)
    {
        if($sourceFileNameVal!= null && $sourceFileNameVal != '' )  { 
            $azureStorage = new AzureStorage() ;
            $utils = new Utils ;
            $euroBusProductName = $utils->getparams('euroBusProductName') ;
            $filePath = $euroBusProductName.$this->modifySourceColumnValue($utils->getparams('euroBusServerInput'),$utils->getparams('euroBusExportOuput'),$requestType) ; 
            $sourceFileName = substr($sourceFileNameVal, 0, strpos($sourceFileNameVal, '_'));
            $file = $azureStorage->getAzureFileList($filePath,$sourceFileName) ;
            return $file ;
        }
    }

    public function getFileAttachmentName($files,$sourceFileNameVal)
    {
        $attachmentFileName = '' ;
        for($i = 0 ; $i<count($files) ; $i++ )
        {  
            if($sourceFileNameVal != $files[$i]->getName() ){

                $fileName = $files[$i]->getName().',' ;
                $attachment = $attachmentFileName == '' ? $fileName : $attachmentFileName.$fileName;
                $attachmentFileName = $attachment ;
            }   
        }
        return $attachmentFileName ;
    }

}


