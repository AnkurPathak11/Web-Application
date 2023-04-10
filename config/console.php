<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'app\commands',
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
		'mailer' => [
            //'class' => 'yii\swiftmailer\Mailer',
			'class' => 'izumi\spoolmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport'=>false,
				
			'transport' => [
                'class' => 'Swift_SmtpTransport',
                 'host' => 'p3plcpnl0897.prod.phx3.secureserver.net',
                'username' => 'ramratan@xtremesoftech.com',
                'password' => 'welcomeram@123',
                'port' => '465',
				'encryption' => 'ssl',
				/* 'host' => 'smtp.gmail.com',
				'username' => 'ashkersomuch@gmail.com',
				'password' => '8561015529',
				'port' => '587',
                'encryption' => 'tls', */

            ],

        ],
		'authManager' => [
            'class' => 'yii\rbac\DbManager',
			//'defaultRoles' => ['guest', 'customer','author'],
        ],
    ],
    'params' => $params,
	'controllerMap' => [
        'mail2' => 'izumi\spoolmailer\MailController',
    ]
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;



