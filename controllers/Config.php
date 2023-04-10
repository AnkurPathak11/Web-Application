<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\filters\VerbFilter;


class Config
{
  
	public function getAuthorization(){	

			return "Basic ". base64_encode("verzekerje_admin:873mRwla2Oq");	
		
	}	
	
	public function getBaseUrl(){
		
			return "http://localhost:8280/CarInsurance/";
		
	}	
	public function getCargoAuthorization()
	{
		return "Basic ". base64_encode("gosselin_admin:94LipO24mjd@108LPO");
	}
	
	public function getDamageApplicationAuthorization()
	{
		return "Basic ". base64_encode("damage_admin:xtgHr4ew3Q#kdaRYna");
	}

	public function getFiscalLegalAuthorization()
	{
		return "Basic ". base64_encode("fiscal_admin:yal56FJw1@TreAlpq");
	}

	}
