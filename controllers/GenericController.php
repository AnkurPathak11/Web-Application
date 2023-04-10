<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use app\controllers\helper\AzureStorage;
//use MicrosoftAzure\Storage\File\FileRestProxy;
//use MicrosoftAzure\Storage\Common\ServiceException;
use app\controllers\helper\RestClientController;
use app\controllers\Config;

class GenericController extends Controller
{
	
	
	
	public function actionDownloadgeneratedfile(){

		$docType=$_GET['doctype'];
		$fileName = $docType;
		$productName = $_GET['productname'];
		//	$connectionString = 'DefaultEndpointsProtocol=https;AccountName=innogarantstorage ;AccountKey=814XVuXMEmgQhsAf1yWto1VG6LSfpYlGhA9Rnkgkcy1QMAxWWyOzq4xfKr0vhgIMtDAsi/7RaasI8nUTUpj/Vg==';
		/*$fileClient = FileRestProxy::createFileService($connectionString);
		$file = $fileClient->getFile("files/".$productName, $fileName);
			$source = stream_get_contents($file->getContentStream());
		//$file = fopen("../files/".$productName."/".$fileName,"r");		
		//$source = stream_get_contents($file);
		header("Content-type: application/octet-stream");
		header("Content-disposition: attachment;filename=".$docType);
		//fclose($file);
		return $source;*/
		return AzureStorage::azurestorage($docType,$fileName,$productName);

	}

	
}	