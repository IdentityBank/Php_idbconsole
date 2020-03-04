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
# Namespace                                                                    #
################################################################################

namespace app\controllers;

################################################################################
# Use(s)                                                                       #
################################################################################

use app\helpers\BillingConfig;
use idbyii2\helpers\IdbAccountNumber;
use idbyii2\helpers\IdbAccountNumberDestination;
use idbyii2\helpers\IdbSecurity;
use idbyii2\helpers\Translate;
use idbyii2\models\db\BillingModel;
use idbyii2\models\db\BillingUserAccount;
use idbyii2\models\db\BillingUserData;
use idbyii2\models\identity\IdbBillingUser;
use Yii;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

################################################################################
# Class(es)                                                                    #
################################################################################

/**
 * Manages IDB Billing users.
 *
 * Tools to manage users and RBAC roles.
 *
 **/
class IdbBillingController extends IdbControllerBase
{

    public $defaultAction = 'stats';

    public function init()
    {
        Yii::$app->getModule('idbuser')->configUserAccount = BillingConfig::get()
                                                                          ->getYii2BillingModulesIdbUserConfigUserAccount(
                                                                          );
        Yii::$app->getModule('idbuser')->configUserData = BillingConfig::get()
                                                                       ->getYii2BillingModulesIdbUserConfigUserData();
        IdbSecurity::$magic_shift_value = BillingConfig::get()->getYii2IdbSecurityMagicShift();
        BillingModel::initModel();
    }

    /**
     * List of billing users stats
     *
     * Display info about users billing.
     *
     **/
    public function actionStats()
    {
        $usersCount = BillingUserAccount::find()->count();

        echo PHP_EOL;
        $this->printSeparator('#');
        echo('IDB billing users stats:' . PHP_EOL);
        $this->printSeparator('#');
        echo("Number of billing users: $usersCount" . PHP_EOL);
        $this->printSeparator('#');
        echo(PHP_EOL);
    }

    /**
     * User data by ID
     *
     * Display info about user with all information stored at the user data.
     *
     **/
    public function actionUserData($uid)
    {
        echo PHP_EOL;
        $this->printSeparator('#');
        echo("User data: [$uid]" . PHP_EOL);
        $this->printSeparator('#');

        $rows = [];
        $models = BillingUserData::find()->where(['uid' => $uid])->all();
        if (is_array($models) && (!empty($models))) {
            foreach ($models as $index => $model) {
                if ($model) {
                    $rows[] = [($index + 1), $model->getKey(), $model->getValue()];
                }
            }
            $roles = Yii::$app->authManager->getRolesByUser($uid);
            if (is_array($roles) && !empty($roles)) {
                $rows[] = [($index + 2), Translate::_('console', 'User roles'), array_keys($roles)];
            }
            echo Table::widget(
                [
                    'headers' => [
                        Translate::_('console', 'No.'),
                        Translate::_('console', 'Key'),
                        Translate::_('console', 'Value')
                    ],
                    'rows' => $rows,
                ]
            );
        } else {
            echo Console::ansiFormat(
                Translate::_('console', 'Missing data for that user.') . PHP_EOL,
                [Console::FG_RED, Console::BOLD]
            );
            $this->printSeparator('#');
            echo PHP_EOL;

            return ExitCode::DATAERR;
        }

        $this->printSeparator('#');
        echo PHP_EOL;

        return ExitCode::OK;
    }

    /**
     * Create new billing user
     *
     * This command allow you to create user and assign RBAC rule for that user.
     *
     * ```
     * idbconsole idb-billing/create <user details>
     * ```
     *
     * @param string $userId        login used to access people
     * @param string $accountNumber dedicated accoint id if not provided we generate it
     * @param string $password      password for login, if not provided we generate password
     *
     * @return int ExitCode::OK on success , error otherwise
     * @throws \Exception
     **/
    public function actionCreate($userId, $accountNumber = null, $password = null)
    {
        $userId = trim($userId);
        if (!empty($accountNumber)) {
            $accountNumber = trim($accountNumber);
            $accountIdObject = new IdbAccountNumber($accountNumber);
            if (!$accountIdObject->isValid()) {
                echo Console::ansiFormat(
                    PHP_EOL . Translate::_('console', 'Invalid account id provided!') . PHP_EOL,
                    [Console::FG_RED, Console::BOLD]
                );
                echo PHP_EOL;

                return ExitCode::DATAERR;
            }
            $login = IdbBillingUser::createLogin($userId, $accountNumber);
            $loginUsed = BillingUserAccount::instantiate()->isLoginUsed($login);
            if ($loginUsed) {
                echo Console::ansiFormat(
                    PHP_EOL . Translate::_('console', 'That login is already in use.') . PHP_EOL,
                    [Console::FG_RED, Console::BOLD]
                );
                echo PHP_EOL;

                return ExitCode::DATAERR;
            }
        }
        if (empty($password)) {
            $idbSecurity = new IdbSecurity(Yii::$app->security);
            $password = $idbSecurity->generateRandomString();
        }

        $index = 1;
        echo PHP_EOL;
        echo Table::widget(
            [
                'headers' => [
                    Translate::_('console', 'No.'),
                    Translate::_('console', 'Key'),
                    Translate::_('console', 'Value')
                ],
                'rows' =>
                    [
                        [$index++, Translate::_('console', 'Login name'), $userId],
                        [$index++, Translate::_('console', 'Account number'), $accountNumber],
                        [$index++, Translate::_('console', 'Password'), $password],
                    ],
            ]
        );

        $userData =
            [
                'password' => $password,
                'userId' => $userId
            ];
        $createIdbUserStatus = IdbBillingUser::create($userId, $userData, $accountNumber);

        if (empty($createIdbUserStatus['uid'])) {
            echo Console::ansiFormat(
                PHP_EOL . Translate::_('console', 'Cannot create user!') . PHP_EOL,
                [Console::FG_RED, Console::BOLD]
            );
            if (!empty($createIdbUserStatus['errors']) && is_array($createIdbUserStatus['errors'])) {
                $this->printSeparator();
                echo PHP_EOL;
                foreach ($createIdbUserStatus['errors'] as $error) {
                    echo Console::ansiFormat(json_encode($error) . PHP_EOL, [Console::FG_RED, Console::BOLD]);
                }
                $this->printSeparator();
            }
            echo PHP_EOL;

            return ExitCode::DATAERR;
        } else {
            $this->actionUserData($createIdbUserStatus['uid']);
        }

        return ExitCode::OK;
    }

    /**
     * Create IDB user
     **/
    public function actionCreateIdb()
    {
        $userId = 'idb';
        $accountId = '1234-AIDB-5678-XMZA';
        $accountNumber = IdbAccountNumber::customAccountNumber(
            $accountId,
            IdbAccountNumberDestination::fromId(IdbAccountNumberDestination::billing)
        );
        $this->actionCreate($userId, $accountNumber, 'idb2018');
        $userAccount = IdbBillingUser::findUserAccountByLogin(IdbBillingUser::createLogin($userId, $accountNumber));
        if ($userAccount) {
            $this->actionUpdateUserData($userAccount->uid, 'email', 'idb@identitybank.eu');

            return ExitCode::OK;
        }

        return ExitCode::DATAERR;
    }

    /**
     * Add/Update user data
     *
     * Add new or update user data.
     *
     **/
    public function actionUpdateUserData($uid, $key, $value)
    {
        $model = BillingUserData::instantiate();
        $model = BillingUserData::find()->where(['uid' => $uid, 'key_hash' => $model->getKeyHash($uid, $key)])->one();
        if (is_null($model)) {
            $model = BillingUserData::instantiate(['uid' => $uid, 'key' => $key, 'value' => $value]);
        } else {
            $model->setAttributes(['uid' => $uid, 'key' => $key, 'value' => $value]);
        }
        if ($model->validate() && $model->save()) {
            $this->actionUserData($uid);

            return ExitCode::OK;
        } else {
            $errors = $model->getErrors();
            if (!empty($errors) && is_array($errors)) {
                $this->printSeparator();
                echo PHP_EOL;
                foreach ($errors as $error) {
                    echo Console::ansiFormat(json_encode($error) . PHP_EOL, [Console::FG_RED, Console::BOLD]);
                }
                $this->printSeparator();
            }

            return ExitCode::DATAERR;
        }
    }

    /**
     * Delete billing data
     **/
    public function actionDeleteUserData($uid, $key)
    {
        $model = BillingUserData::instantiate();
        $model = BillingUserData::find()->where(['uid' => $uid, 'key_hash' => $model->getKeyHash($uid, $key)])->one();
        if (is_null($model)) {
            $this->actionUserData($uid);

            return ExitCode::OK;
        }
        if ($model->delete()) {
            $this->actionUserData($uid);

            return ExitCode::OK;
        } else {
            $errors = $model->getErrors();
            if (!empty($errors) && is_array($errors)) {
                $this->printSeparator();
                echo PHP_EOL;
                foreach ($errors as $error) {
                    echo Console::ansiFormat(json_encode($error) . PHP_EOL, [Console::FG_RED, Console::BOLD]);
                }
                $this->printSeparator();
            }

            return ExitCode::DATAERR;
        }
    }
}

################################################################################
#                                End of file                                   #
################################################################################
