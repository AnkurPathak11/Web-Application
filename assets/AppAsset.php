<?php

//$params = require __DIR__ . '/params.php';
if (ENV == 'development') $params = require __DIR__ . '/params_dev.php';
if (ENV == 'production') $params = require __DIR__ . '/params_prod.php';
if (ENV == 'qa') $params = require __DIR__ . '/params_qa.php';
if (ENV == 'local') $params = require __DIR__ . '/params_local.php';

//$db = require __DIR__ . '/db.php';
//if (ENV == 'development') $db = require __DIR__ . '/db_dev.php';
//if (ENV == 'production') $db = require __DIR__ . '/db_prod.php';
//if (ENV == 'qa') $db = require __DIR__ . '/db_qa.php';
//if (ENV == 'local') $db = require __DIR__ . '/db_local.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'extensions' => require __DIR__ . '/../vendor/yiisoft/extensions.php',
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
	'language' => 'nl-EN',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'hbUsZd9mNTJifqtA_JvkWExoLLwcj6VL',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
			'identityClass' => 'app\models\Clients',
            'enableAutoLogin' => true,
		    'enableSession' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
          //  'class' => 'yii\swiftmailer\Mailer',
			'class' => 'izumi\spoolmailer\Mailer',
		//	'class' => 'luya\mailjet\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
                        'useFileTransport'=>false,
			//mail jet config
			// 'class' => 'weluse\mailjet\Mailer',
			//'apikey' => '87b0f8c17f164821791fc9156fc5f54d',
			//'secret' => 'e7ef2b6001e0f668c87b56c0ff429852',
			'transport' => [
                'class' => 'Swift_SmtpTransport',
				/* 'host' => 'smtp.gmail.com',
				'username' => 'ashkersomuch@gmail.com',
				'password' => '8561015529',
				'port' => '587',
                'encryption' => 'tls', */
			/*	 'host' => 'p3plcpnl0897.prod.phx3.secureserver.net',
                'username' => 'ramratan@xtremesoftech.com',
                'password' => 'welcomeram@123',
                'port' => '465',
				'encryption' => 'ssl',  */
				'host' => 'in-v3.mailjet.com',
                'username' => '87b0f8c17f164821791fc9156fc5f54d',
                'password' => 'e7ef2b6001e0f668c87b56c0ff429852',
                'port' => '587',
				'encryption' => 'tls',
            ],
        ],
		'authManager' => [
            'class' => 'yii\rbac\DbManager',
			//'defaultRoles' => ['guest', 'customer','author'],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        //'db' => $db,
       'urlManager' => [
			'enablePrettyUrl' => false,
			'showScriptName' => true,
                        'enableStrictParsing' => false,
			'rules' => [
//				'<controller:\w+>/<id:\d+>' => '<controller>/view',
                                '<controller:\w+>/<action:\w+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>/<id:\d+>/<usr:\d+>' => '<controller>/<action>',
			],
		],
        'formatter_be' => [
        'class' => 'yii\i18n\Formatter',
        'thousandSeparator'=>'.',
        'decimalSeparator'=>','
    ],
    ],
    'params' => $params,
	'controllerMap' => ['mail2' => 'izumi\spoolmailer\MailController',
    ],

	// added for export excel
	'modules' => [
     'gridview' => ['class' => 'kartik\grid\Module'],
 'cargoinsurance'=>[
    'class' => 'app\modules\cargoinsurance\CargoInsurance',
],
'cibrequest'=>[
    'class' => 'app\modules\cibrequest\CibRequest',
],
'customermanagement'=>[
    'class' => 'app\modules\customermanagement\CustomerManagement',
],
'usermanagement'=>[
    'class' => 'app\modules\usermanagement\UserManagement',
],
'roundSwitch' => [
    'class' => 'nickdenry\grid\toggle\Module',
]
],

// api configuration for url manager
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}
if(ENV == 'development' || ENV == 'local')
{
   // configuration adjustments for 'dev' environment
    $config['components']['db'] = $db;
}

if(ENV == 'qa')
{
   // configuration adjustments for 'qa' environment
    $config['components']['db'] = $db;
}

if(ENV == 'production')
{
   // configuration adjustments for 'prod' environment
    $config['components']['db'] = $db;
}

return $config;