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

use idbyii2\helpers\IdbAccountNumber;
use idbyii2\helpers\IdbAccountNumberDestination;
use idbyii2\helpers\IdbSecurity;
use idbyii2\helpers\Translate;
use idbyii2\models\db\BusinessUserAccount;
use idbyii2\models\db\BusinessUserData;
use idbyii2\models\db\SearchBusinessUserData;
use idbyii2\models\identity\IdbBusinessUser;
use Yii;
use yii\console\ExitCode;
use yii\console\widgets\Table;
use yii\helpers\Console;

################################################################################
# Class(es)                                                                    #
################################################################################

/**
 * Manages IDB Business users.
 *
 * Tools to manage users and RBAC roles.
 *
 **/
class IdbUserController extends IdbControllerBase
{

    public $defaultAction = 'stats';

    /**
     * List of business users stats
     *
     * Display info about users business.
     *
     **/
    public function actionStats()
    {
        $usersCount = BusinessUserAccount::find()->count();
        $idbAdminUsers = Yii::$app->authManager->getUserIdsByRole('idb_admin');
        $idbAdminUsersCount = count($idbAdminUsers);
        $idbUsers = Yii::$app->authManager->getUserIdsByRole('idb_staff');
        $idbUsersCount = count($idbUsers);

        echo PHP_EOL;
        $this->printSeparator('#');
        echo('IDB business users stats:' . PHP_EOL);
        $this->printSeparator('#');
        echo(PHP_EOL);
        echo("Number of business users: $usersCount" . PHP_EOL);
        echo("Number of business admin users: $idbAdminUsersCount" . PHP_EOL);
        echo("Number of business IDB users: $idbUsersCount" . PHP_EOL);

        $headers = [
            Translate::_('console', 'No.'),
            Translate::_('console', 'User UUID'),
            Translate::_('console', 'Login name'),
            Translate::_('console', 'Account number')
        ];

        echo PHP_EOL;
        $this->printSeparator();
        echo('Administrators:' . PHP_EOL);
        $this->printSeparator();
        echo PHP_EOL;
        $rows = [];
        $userDataSearch = BusinessUserData::instantiate();
        foreach ($idbAdminUsers as $index => $idbAdminUser) {
            $userId = $accountNumber = null;
            $models = BusinessUserData::find()->where(
                [
                    'uid' => $idbAdminUser,
                    'key_hash' =>
                        [
                            $userDataSearch->getKeyHash($idbAdminUser, 'userId'),
                            $userDataSearch->getKeyHash($idbAdminUser, 'accountNumber'),
                        ]
                ]
            )->all();
            foreach ($models as $model) {
                if ($model) {
                    if ($model->getKey() === 'userId') {
                        $userId = $model->getValue();
                    }
                    if ($model->getKey() === 'accountNumber') {
                        $accountNumber = $model->getValue();
                    }
                }
            }
            $rows[] = [($index + 1), $idbAdminUser, $userId, $accountNumber];
        }
        echo Table::widget(
            [
                'headers' => $headers,
                'rows' => $rows,
            ]
        );

        echo PHP_EOL;
        $this->printSeparator();
        echo('IDB users:' . PHP_EOL);
        $this->printSeparator();
        echo PHP_EOL;
        $rows = [];
        foreach ($idbUsers as $index => $idbUser) {
            $userId = $accountNumber = null;
            $models = BusinessUserData::find()->where(
                [
                    'uid' => $idbUser,
                    'key_hash' =>
                        [
                            $userDataSearch->getKeyHash($idbUser, 'userId'),
                            $userDataSearch->getKeyHash($idbUser, 'accountNumber'),
                        ]
                ]
            )->all();
            foreach ($models as $model) {
                if ($model) {
                    if ($model->getKey() === 'userId') {
                        $userId = $model->getValue();
                    }
                    if ($model->getKey() === 'accountNumber') {
                        $accountNumber = $model->getValue();
                    }
                }
            }
            $rows[] = [($index + 1), $idbUser, $userId, $accountNumber];
        }
        echo Table::widget(
            [
                'headers' => $headers,
                'rows' => $rows,
            ]
        );

        echo PHP_EOL;
        $this->printSeparator('#');
        echo PHP_EOL;
    }

    /**
     * Get user ID by login
     *
     * Find user ID base on login details: User ID and Account Number
     *
     **/
    public function actionUserId($userId, $accountNumber)
    {
        $userId = trim($userId);
        $accountNumber = trim($accountNumber);
        $login = IdbBusinessUser::createLogin($userId, $accountNumber);
        $userAccount = IdbBusinessUser::findUserAccountByLogin($login);
        if ($userAccount) {
            $this->printSeparator('#');
            echo Console::ansiFormat(
                Translate::_('console', 'Login name')
                . ": $userId" . PHP_EOL,
                [Console::FG_GREEN, Console::FG_YELLOW]
            );
            echo Console::ansiFormat(
                Translate::_('console', 'Account number')
                . ": $accountNumber" . PHP_EOL,
                [Console::FG_GREEN, Console::FG_YELLOW]
            );
            echo Console::ansiFormat(
                Translate::_('console', 'User UUID')
                . ": [$userAccount->uid]" . PHP_EOL,
                [Console::FG_GREY, Console::BOLD]
            );
            $this->printSeparator('#');

            return ExitCode::OK;
        }

        return ExitCode::DATAERR;
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
        $models = BusinessUserData::find()->where(['uid' => $uid])->all();

        if (is_array($models) && (!empty($models))) {
            foreach ($models as $index => $model) {
                if ($model) {
                    $value = ($model->getValue() ?? '');
                    $value = mb_strimwidth($value, 0, 80, '...');
                    $rows[] = [($index + 1), $model->getKey(), $value];
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
     * Create account number based on provided account id
     *
     * @param string  $accountId          IDB account ID
     * @param integer $accountDestination select portal destination:
     *                                    1 - 'business'
     *                                    2 - 'people'
     *                                    3 - 'admin'
     *                                    4 - 'billing'
     *
     * Info:
     * when Account number is Green then is valid when is RED then is not valid Account number
     *
     * @return integer OK when Account number is valid DATAERR otherwise
     **/
    public function actionAccountNumberBasedOnAccountId($accountId, $accountDestination = 1)
    {
        $accountNumber = IdbAccountNumber::customAccountNumber(
            $accountId,
            IdbAccountNumberDestination::fromId($accountDestination)
        );
        echo Console::ansiFormat(
            Translate::_('console', 'Account ID')
            . ": $accountId" . PHP_EOL,
            [Console::FG_YELLOW, Console::BOLD]
        );
        $accountNumber = new IdbAccountNumber($accountNumber);
        $valid = $accountNumber->isValid();
        if ($valid) {
            echo Console::ansiFormat(
                Translate::_('console', 'Account number')
                . ": $accountNumber" . PHP_EOL,
                [Console::FG_GREEN, Console::BOLD]
            );

            return ExitCode::OK;
        } else {
            echo Console::ansiFormat(
                Translate::_('console', 'Account number')
                . ": $accountNumber" . PHP_EOL,
                [Console::FG_RED, Console::BOLD]
            );

            return ExitCode::DATAERR;
        }
    }

    /**
     * Create new business user
     *
     * This command allow you to create user and assign RBAC rule for that user.
     *
     * ```
     * idbconsole idb-user/create <user details>
     * ```
     *
     * @param string $userId        login used to access business
     * @param string $accountNumber dedicated accoint id if not provided we generate it
     * @param string $password      password for login, if not provided we generate password
     *
     * @return int
     * @throws \Exception
     */
    public function actionCreate($userId, $accountNumber = null, $password = null)
    {
        $userId = trim($userId);
        if (!empty($accountNumber)) {
            $accountNumber = trim($accountNumber);
            $accountIdObject = new IdbAccountNumber($accountNumber);
            if (!$accountIdObject->isValid()) {
                echo Console::ansiFormat(
                    PHP_EOL .
                    Translate::_('console', 'Invalid account id provided!')
                    . PHP_EOL,
                    [Console::FG_RED, Console::BOLD]
                );
                echo PHP_EOL;

                return ExitCode::DATAERR;
            }
            $login = IdbBusinessUser::createLogin($userId, $accountNumber);
            $loginUsed = BusinessUserAccount::instantiate()->isLoginUsed($login);
            if ($loginUsed) {
                echo Console::ansiFormat(
                    PHP_EOL .
                    Translate::_('console', 'That login is already in use.')
                    . PHP_EOL,
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
                        [$index++, Translate::_('console', 'User ID'), $userId],
                        [$index++, Translate::_('console', 'Account ID'), $accountNumber],
                        [$index++, Translate::_('console', 'Password'), $password],
                    ],
            ]
        );

        $userData =
            [
                'password' => $password,
                'userId' => $userId
            ];
        $createIdbUserStatus = IdbBusinessUser::create($userId, $userData, $accountNumber);

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
            IdbAccountNumberDestination::fromId(IdbAccountNumberDestination::business)
        );
        $this->actionCreate($userId, $accountNumber, 'idb2018');
        $userAccount = IdbBusinessUser::findUserAccountByLogin(IdbBusinessUser::createLogin($userId, $accountNumber));
        if ($userAccount) {
            $this->actionUpdateUserData($userAccount->uid, 'email', 'idb@identitybank.eu');
            $this->actionAssignRole('idb_admin', $userAccount->uid);

            return ExitCode::OK;
        }

        return ExitCode::DATAERR;
    }

    /**
     * Create new business user step by step
     **/
    public function actionCreateWizard()
    {
        $userId = Console::prompt("Login name: ");
        $accountNumber = Console::prompt("Account number (keep empty to autogenerate): ");
        $password = Console::prompt("Password: ");
        $this->actionCreate($userId, $accountNumber, $password);
        $userAccount = IdbBusinessUser::findUserAccountByLogin(IdbBusinessUser::createLogin($userId, $accountNumber));
        if ($userAccount) {
            return ExitCode::OK;
        }

        return ExitCode::DATAERR;
    }

    /**
     * List available roles
     **/
    public function actionListRoles()
    {
        $roles = Yii::$app->authManager->getRoles();
        if (is_array($roles) && !empty($roles)) {
            $headers = [
                Translate::_('console', 'No.'),
                Translate::_('console', 'Name'),
                Translate::_('console', 'Description'),
                Translate::_('console', 'Created At'),
                Translate::_('console', 'Updated At')
            ];
            $rows = [];
            $index = 1;
            foreach ($roles as $roleName => $roleData) {
                $rows[] = [
                    $index++,
                    $roleData->name,
                    $roleData->description,
                    $roleData->createdAt,
                    $roleData->updatedAt
                ];
            }

            $this->printSeparator();
            echo Translate::_('console', 'Available roles: ') . count($roles) . PHP_EOL;
            $this->printSeparator();
            echo Table::widget(
                [
                    'headers' => $headers,
                    'rows' => $rows,
                ]
            );
            $this->printSeparator();
        }

        return ExitCode::OK;
    }

    /**
     * Assign role for user
     **/
    public function actionAssignRole($role_name, $uid)
    {
        if (!empty($uid) && !empty($role_name)) {
            $roles = Yii::$app->authManager->getRoles();
            if (!empty($roles[$role_name])) {
                $user_roles = Yii::$app->authManager->getRolesByUser($uid);
                if (!(is_array($user_roles) && in_array($role_name, array_keys($user_roles)))) {
                    $assign = Yii::$app->authManager->assign($roles[$role_name], $uid);
                    if (!empty($assign)) {
                        $headers = [
                            Translate::_('console', 'User UUID'),
                            Translate::_('console', 'Role Name'),
                            Translate::_('console', 'Created At')
                        ];
                        $rows = [[$assign->userId, $assign->roleName, $assign->createdAt]];
                        $this->printSeparator();
                        echo Table::widget(
                            [
                                'headers' => $headers,
                                'rows' => $rows,
                            ]
                        );
                        $this->printSeparator();
                    }
                }
                $this->actionUserData($uid);

                return ExitCode::OK;
            }
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
        $model = BusinessUserData::instantiate();
        $model = BusinessUserData::find()->where(['uid' => $uid, 'key_hash' => $model->getKeyHash($uid, $key)])->one();
        if (is_null($model)) {
            $model = BusinessUserData::instantiate(['uid' => $uid, 'key' => $key, 'value' => $value]);
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
     * Delete user data
     **/
    public function actionDeleteUserData($uid, $key)
    {
        $model = BusinessUserData::instantiate();
        $model = BusinessUserData::find()->where(['uid' => $uid, 'key_hash' => $model->getKeyHash($uid, $key)])->one();
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


    /**
     * Refresh search user data
     *
     * @param string $uid user id, when empty then refresh all users.
     *
     * @return int
     */
    public function actionRefreshSearchUserData($uid)
    {
        if (empty($uid)) {
            echo(Translate::_('console', "That action will clear all current search data.") . PHP_EOL);
            if ($this->confirm(Translate::_('console', "Are you sure?"))) {
                echo(Translate::_('console', "Refreshing search data ...") . PHP_EOL);
                SearchBusinessUserData::refreshAll();
            }
        } else {
            $model = IdbBusinessUser::findIdentity($uid);
            if ($model) {
                echo($model->toString() . PHP_EOL);
                echo(Translate::_('console', "Refreshing search data ...") . PHP_EOL);
                SearchBusinessUserData::refreshUser($uid);
            }
        }

        return ExitCode::OK;
    }
}

################################################################################
#                                End of file                                   #
################################################################################
