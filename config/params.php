<?php

return [
   // 'adminEmail' => 'testemail11044@gmail.com',

	'azureConnectionString'=>env('CLOUD_CONNECTION_STRING'),
	'logs_dir' => env('LOGS_DIRECTORY'),
	'fileShare' => env('FILE_SHARE'),
	'excepReqBasePath' => env('EXCEP_REQ_BASE_PATH') ,
	'reportWithoutPivotPath' => env('REPORT_WITHOUT_PIVOT_PATH'),
	'CIBAPIAuthorization'=> env('CIB_API_AUTHORIZATION'),
	'from'=>env('FROM'),
	'templateId'=>(int)env('TEMPLATE_ID'),
	'link' =>env('LINK'),
	'subject'=>env('SUBJECT'),
	'cc'=>env('CC'),
	'passwordForgetAPIUrl'=>env('PASSWORD_FORGET_API_URL'),
	'MailjetAPIKey'=>env('MAILJET_API_KEY'),
	'MailjetAPISecretKey'=>env('MAILJET_API_SECRET_KEY'),
	'reportWithoutPivotPath' => env('REPORT_WITHOUT_PIVOT_PATH'),
	 'getTariff' => env('GET_TARIFF_JSON_API'),
   'getTotalPremium' => env('GET_TOTAL_PREMIUM_API'),
   'policyGenerate' => env('POLICY_GENERATE_API'),
   'civilAuthorizaztion' => env('CIVIL_AUTHORIZATION'),
	'reportWithoutPivotPath' => env('REPORT_WITHOUT_PIVOT_PATH'),
	'euroBusServerInput' => env('EURO_BUS_SERVER_INPUT'),
	'euroBusBrioImport' => env('EURO_BUS_BRIO_IMPORT'),
	'euroBusExportOuput'=> env('EURO_BUS_EXPORT_OUTPUT'),
	'euroBusProductName'=> env('EURO_BUS_PRODUCT_NAME'),
   'clientGroupsType' => env('CLIENT_GROUPS_TYPE'),
		'euroBusFailureStatus' => ['Acknowledgement Failure','Send mail failure','Brio import csv file generation failure','Brio txt file generation failure','Files attachment failure','Damage txt file generation failure','Brio export csv file generation failure','Brio import csv rows generation failure'],
	'euroBusSuccessStatus' => ['Acknowledgement sent','Brio import csv file generated successfully','Brio txt file generated successfully','Damage txt file generated successfully','Mail sent successfully','Brio export csv file generated successfully','Brio import csv rows generated successfully'],
	'APIBaseUrl'=> env('API_BASE_URL'),
	'euroBusAPIReplay' => env('EURO_BUS_API_REPLAY') ,
	'euroBusAPIAuthorization' => env('EURO_BUS_API_AUTHORIZATION') ,
	'envType' => env('ENV_TYPE'),
	'euroBusReplayPath' => env('EURO_BUS_REPLAY_PATH'),
	'euroBusAPIReplaySingleFile' => env('EURO_BUS_API_REPLAY_SINGLE_FILE'),
	'euroBusTransformationType' => ['EBTOBRIO'=>'EBTOCC','BRIOTOEB'=>'CCTOEB'],
	'euroBusLocalOutputPath' => env('EURO_BUS_LOCAL_OUTPUT_PATH'),
	'euroBusEBTOCC'=> env('EURO_BUS_EBTOCC'),
	'euroBusCCTOEB'=> env('EURO_BUS_CCTOEB'),
	'user_Roles' => explode(",",env('USER_ROLES')),
  'cyberSecurityProductId'=> env('CYBER_SECURITY_PRODUCT_ID'),
  'cyberSecurityProductName'=> env('CYBER_SECURITY_PRODUCT_NAME'),
	'euroBusProdTemplateId' => (int) env('EURO_BUS_PROD_TEMPLATE_ID'),
	'euroBusClaimTemplateId' => (int) env('EURO_BUS_CLAIM_TEMPLATE_ID'),
	'euroBusFromEmail' => env('EURO_BUS_FROM_EMAIL'),
	'euroBusToEmail' => env('EURO_BUS_TO_EMAIL'),
	'euroAPIBaseUrl' => env('EURO_API_BASE_URL')
];