<?php
# * ********************************************************************* *
# *                                                                       *
# *   PHP IDB console tools                                               *
# *   This file is part of idbconsole. This project may be found at:      *
# *   https://github.com/IdentityBank/Php_idbconsole.                     *
# *                                                                       *
# *   Copyright (C) 2020 by Identity Bank. All Rights Reserved.           *
# *   https://www.identitybank.eu - You belong to you                     *
# *                                                                       *
# *   This program is free software: you can redistribute it and/or       *
# *   modify it under the terms of the GNU Affero General Public          *
# *   License as published by the Free Software Foundation, either        *
# *   version 3 of the License, or (at your option) any later version.    *
# *                                                                       *
# *   This program is distributed in the hope that it will be useful,     *
# *   but WITHOUT ANY WARRANTY; without even the implied warranty of      *
# *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the        *
# *   GNU Affero General Public License for more details.                 *
# *                                                                       *
# *   You should have received a copy of the GNU Affero General Public    *
# *   License along with this program. If not, see                        *
# *   https://www.gnu.org/licenses/.                                      *
# *                                                                       *
# * ********************************************************************* *

################################################################################
# Use(s)                                                                       #
################################################################################

use app\helpers\BusinessConfig;
use idbyii2\helpers\IdbYii2Config;
use function xmz\simplelog\logLevel;

################################################################################
# Load params                                                                  #
################################################################################

$params = require(__DIR__ . '/params.php');

################################################################################
# Web Config                                                                   #
################################################################################

$config =
    [
        'id' => 'idbconsole',
        'basePath' => dirname(__DIR__),
        'language' => BusinessConfig::get()->getConsoleLanguage(),
        'sourceLanguage' => 'en-GB',
        'aliases' =>
            [
                '@idbyii2' => '/usr/local/share/p57b/php/idbyii2',
            ],
        'modules' =>
            [
                'idbuser' =>
                    [
                        'class' => 'idbyii2\modules\idbuser\IdbUserModule',
                        'configUserAccount' => BusinessConfig::get()->getYii2BusinessModulesIdbUserConfigUserAccount(),
                        'configUserData' => BusinessConfig::get()->getYii2BusinessModulesIdbUserConfigUserData(),
                    ],
            ],
        'controllerMap' =>
            [
                'fixture' =>
                    [
                        'class' => 'yii\console\controllers\FixtureController',
                        'namespace' => 'common\fixtures',
                    ],
                'idb' =>
                    [
                        'class' => 'app\controllers\IdbController',
                    ],
            ],
        'components' =>
            [
                'cache' => [
                    'class' => 'yii\caching\FileCache',
                ],
                'cacheApc' => [
                    'class' => 'yii\caching\ApcCache'
                ],
                'cacheDB' => [
                    'class' => 'yii\caching\DbCache'
                ],
                'idbpeopleportalapi' =>
                    [
                        'class' => 'idbyii2\components\PortalApi',
                        'configuration' => BusinessConfig::get()->getPeoplePortalApiConfiguration(),
                    ],
                'idbbusinessportalapi' =>
                    [
                        'class' => 'idbyii2\components\PortalApi',
                        'configuration' => BusinessConfig::get()->getBusinessPortalApiConfiguration(),
                    ],
                'idbankclientbusiness' =>
                    [
                        'class' => 'idbyii2\models\idb\IdbBankClientBusiness',
                        'service' => 'business',
                        'host' => BusinessConfig::get()->getIdBankHost(),
                        'port' => BusinessConfig::get()->getIdBankPort(),
                        'configuration' => BusinessConfig::get()->getIdBankConfiguration()
                    ],
                'idbankclientpeople' =>
                    [
                        'class' => 'idbyii2\models\idb\IdbBankClientPeople',
                        'service' => 'people',
                        'host' => PeopleConfig::get()->getIdBankHost(),
                        'port' => PeopleConfig::get()->getIdBankPort(),
                        'configuration' => PeopleConfig::get()->getIdBankConfiguration()
                    ],
                'idbillclient' =>
                    [
                        'class' => 'idbyii2\models\idb\BusinessIdbBillingClient',
                        'billingName' => BusinessConfig::get()->getIdBillingName(),
                        'host' => BusinessConfig::get()->getIdBillHost(),
                        'port' => BusinessConfig::get()->getIdBillPort(),
                        'configuration' => BusinessConfig::get()->getIdBillConfiguration()
                    ],
                'idbstorageclient' =>
                    [
                        'class' => 'idbyii2\models\idb\IdbStorageClient',
                        'storageName' => IdbYii2Config::get()->getIdbStorageName(),
                        'host' => IdbYii2Config::get()->getIdbStorageHost(),
                        'port' => IdbYii2Config::get()->getIdbStoragePort(),
                        'configuration' => IdbYii2Config::get()->getIdbStorageConfiguration()
                    ],
                'idbmessenger' =>
                    [
                        'class' => 'idbyii2\components\Messenger',
                        'configuration' => BusinessConfig::get()->getMessengerConfiguration(),
                    ],
                'idbrabbitmq' =>
                    [
                        'class' => 'idbyii2\components\IdbRabbitMq',
                        'host' => BusinessConfig::get()->getIdbRabbitMqHost(),
                        'port' => BusinessConfig::get()->getIdbRabbitMqPort(),
                        'user' => BusinessConfig::get()->getIdbRabbitMqUser(),
                        'password' => BusinessConfig::get()->getIdbRabbitMqPassword()
                    ],
                'db' => require(YII_P57B_BUSINESS_DIR_CONFIG . '/db_p57b_business.php'), //RBAC
                'p57b_business' => require(YII_P57B_BUSINESS_DIR_CONFIG . '/db_p57b_business.php'),
                'p57b_business_search' => require(YII_P57B_BUSINESS_DIR_CONFIG . '/db_p57b_business.php'),
                'p57b_people' => require(YII_P57B_PEOPLE_DIR_CONFIG . '/db_p57b_people.php'),
                'p57b_people_search' => require(YII_P57B_PEOPLE_DIR_CONFIG . '/db_p57b_people.php'),
                'p57b_billing' => require(YII_P57B_BILLING_DIR_CONFIG . '/db_p57b_billing.php'),
                'p57b_billing_search' => require(YII_P57B_BILLING_DIR_CONFIG . '/db_p57b_billing.php'),
                'authManager' =>
                    [
                        'class' => 'yii\rbac\DbManager',
                    ],
                'log' =>
                    [
                        'traceLevel' => YII_DEBUG ? 3 : 0,
                        'targets' =>
                            [
                                [
                                    'class' => 'yii\log\FileTarget',
                                    'levels' => ['error'],
                                ],
                                [
                                    'class' => 'yii\log\FileTarget',
                                    'logVars' => [],
                                    'categories' => ['console'],
                                    'levels' => ['info'],
                                    'logFile' => '@runtime/logs/info.log',
                                    'maxFileSize' => 1024 * 2,
                                    'maxLogFiles' => 10,
                                ],
                                [
                                    'class' => 'yii\log\FileTarget',
                                    'logVars' => [],
                                    'levels' => ['error', 'warning'],
                                    'logFile' => '/var/log/p57b/p57b.console-errors.log',
                                ],
                                [
                                    'class' => 'yii\log\FileTarget',
                                    'logVars' => [],
                                    'levels' => ['trace', 'info'],
                                    'logFile' => '/var/log/p57b/p57b.console-debug.log',
                                ],
                            ],
                    ],
                'i18n' =>
                    [
                        'translations' =>
                            [
                                'console' =>
                                    [
                                        'class' => 'yii\i18n\PhpMessageSource',
                                        'forceTranslation' => true,
                                        'sourceLanguage' => 'en-GB',
                                        'basePath' => '@app/messages',
                                    ],
                                'idbyii2' =>
                                    [
                                        'class' => 'yii\i18n\PhpMessageSource',
                                        'forceTranslation' => true,
                                        'sourceLanguage' => 'en-GB',
                                        'basePath' => '@idbyii2/messages',
                                    ],
                                'idbexternal' =>
                                    [
                                        'class' => 'yii\i18n\PhpMessageSource',
                                        'forceTranslation' => true,
                                        'sourceLanguage' => 'en-GB',
                                        'basePath' => '@idbyii2/messages',
                                    ],
                            ],
                    ],
            ],
        'params' => $params,
    ];

// Use business config log level
logLevel(BusinessConfig::get()->getLogLevel());

$config['bootstrap'] = ['log'];
$config['on beforeRequest'] = function ($event) {
    idbyii2\models\db\BusinessModel::initModel();
};

return $config;

################################################################################
#                                End of file                                   #
################################################################################
