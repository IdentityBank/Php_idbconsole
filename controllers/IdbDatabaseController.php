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

use app\helpers\BusinessConfig;
use app\helpers\PeopleConfig;
use DateTime;
use idbyii2\helpers\IdbDatabaseHelper;
use idbyii2\helpers\IdbYii2Config;
use idbyii2\helpers\Localization;
use idbyii2\models\db\BusinessDatabaseMigrationInternals;
use idbyii2\models\db\PeopleDatabaseMigrationInternals;
use Yii;
use yii\console\ExitCode;
use yii\helpers\Console;

################################################################################
# Class(es)                                                                    #
################################################################################

/**
 * Migrations for IDBank API
 *
 * Actions that allow you to migrate current Identity Bank databases.
 *
 **/
class IdbDatabaseController extends IdbControllerBase
{

    /**
     * @param \yii\base\Action $action - executed action
     *
     * @return bool
     */
    public function beforeAction($action)
    {
        if (strtoupper(IdbYii2Config::get()->idbApiMigrationEnabled()) === 'IDB_ALLOW_MIGRATION') {
            return parent::beforeAction($action);
        } else {
            echo PHP_EOL;
            echo Console::ansiFormat(
                "Migration is not enabled for this server!" . PHP_EOL,
                [Console::FG_RED, Console::BOLD]
            );
            echo PHP_EOL;
        }

        return false;
    }

    /**
     * @param $migrationName - name/id of current migration
     * @param $version       - version of current migration
     *
     * @throws \Exception
     */
    private function migrationHeader($migrationName, $version = null)
    {
        $time = Localization::getDateTimePortalFormat(new DateTime(), true);
        echo PHP_EOL;
        $this->printSeparator('#');
        echo Console::ansiFormat(
            "Start IDB database action - $migrationName"
            . PHP_EOL,
            [Console::FG_RED, Console::BOLD]
        );
        if (!empty($version)) {
            $this->printSeparator('-');
            echo("Migration version: [#$version]" . PHP_EOL);
        }
        $this->printSeparator('#');
        echo Console::ansiFormat(
            "Start time: [$time]"
            . PHP_EOL,
            [Console::FG_GREEN, Console::BOLD]
        );
        $this->printSeparator('#');
        echo PHP_EOL;
    }

    /**
     * @param       $migrationName - name/id of current migration
     * @param       $timeInSecs    - duration of migration in seconds
     * @param array $messages      - array of messages for logs
     *
     * @throws \Exception
     */
    private function migrationFooter($migrationName, $timeInSecs, $messages = [])
    {
        $time = Localization::getDateTimePortalFormat(new DateTime(), true);
        echo PHP_EOL;
        $this->printSeparator('#');
        foreach ($messages as $message) {
            if (!empty($message)) {
                echo($message . PHP_EOL);
            }
        }
        echo("Executed in $timeInSecs seconds" . PHP_EOL);
        $this->printSeparator('-');
        echo Console::ansiFormat(
            "End time: [$time]"
            . PHP_EOL,
            [Console::FG_GREEN, Console::BOLD]
        );
        $this->printSeparator('#');
        echo Console::ansiFormat(
            "Migration finished - $migrationName"
            . PHP_EOL,
            [Console::FG_RED, Console::BOLD]
        );
        $this->printSeparator('#');
        echo PHP_EOL;
    }

    /**
     * @param $message - message to report to the logs
     */
    private function migrationError($message)
    {
        $message = json_encode($message);
        echo Console::ansiFormat(
            $message . PHP_EOL,
            [Console::FG_RED, Console::BOLD]
        );
        Yii::error($message);
    }

    /**
     * Check all tables and report if anything is missing.
     *
     * @return int
     * @throws \Exception
     */
    public function actionIdbValidation()
    {
        return IdbDatabaseHelper::executeIdbValidation($this);
    }

    /**
     * Execute all migrations.
     *
     * @return int execution status
     * @throws \Exception
     */
    public function actionIdbMigration()
    {
        $migrationName = "IDB Migrations";
        $this->migrationHeader($migrationName);
        $start = microtime(true);

        $this->actionIdbMigrationBusiness();
        $this->actionIdbMigrationPeople();

        $this->migrationFooter($migrationName, (microtime(true) - $start));

        return ExitCode::OK;
    }

    /**
     * Execute all business migrations.
     *
     * @param int $version - migration version of the table
     *
     * @return int execution status
     * @throws \Exception
     */
    public function actionIdbMigrationBusiness($version = null)
    {
        $migrationName = "IDB Business Migration";
        $businessMigrationUrl = BusinessConfig::get()->getIdbBusinessMigrationPath();
        if (substr($businessMigrationUrl, -1) !== '/') {
            $businessMigrationUrl .= DIRECTORY_SEPARATOR;
        }
        $this->migrationHeader($migrationName, $version);
        $start = microtime(true);
        $this->message("Migration path: $businessMigrationUrl");

        $model = BusinessDatabaseMigrationInternals::find()->where(['db' => 'idb_business'])->one();
        $migrationStatus = $this->executeMigrations($businessMigrationUrl, $model);

        $this->migrationFooter($migrationName, (microtime(true) - $start));

        return $migrationStatus;
    }

    /**
     * Execute all people migrations.
     *
     * @param int $version - migration version of the table
     *
     * @return int execution status
     * @throws \Exception
     */
    public function actionIdbMigrationPeople($version = null)
    {
        $migrationName = "IDB People Migration";
        $peopleMigrationUrl = PeopleConfig::get()->getIdbPeopleMigrationPath();
        if (substr($peopleMigrationUrl, -1) !== '/') {
            $peopleMigrationUrl .= DIRECTORY_SEPARATOR;
        }
        $this->migrationHeader($migrationName, $version);
        $start = microtime(true);
        $this->message("Migration path: $peopleMigrationUrl");

        $model = PeopleDatabaseMigrationInternals::find()->where(['db' => 'idb_people'])->one();
        $migrationStatus = $this->executeMigrations($peopleMigrationUrl, $model);


        $this->migrationFooter($migrationName, (microtime(true) - $start));

        return $migrationStatus;
    }

    /**
     * @param $migrationPath
     * @param $migrationDbModel
     *
     * @return int
     */
    private function executeMigrations($migrationPath, $migrationDbModel)
    {
        $migrationScriptPathTemplate = 'idb-upgrade-rev%05d.inc';
        if ($migrationDbModel) {
            $currentVersion = $migrationDbModel->version;
            $this->message("Migration current version: $currentVersion");
            $latestVersion = IdbDatabaseHelper::getLatestMigrationVersion($migrationPath);
            $this->message("Migration latest version: $latestVersion");
            while ($currentVersion < $latestVersion) {
                $currentVersion += 1;
                $migrationScriptPath = sprintf($migrationScriptPathTemplate, $currentVersion);
                $migrationScriptPath = $migrationPath . $migrationScriptPath;
                echo PHP_EOL;
                $this->printSeparator('*');
                $this->message("Execute migration script: $migrationScriptPath");
                $this->printSeparator('*');
                echo PHP_EOL;
                if (
                    is_file($migrationScriptPath)
                    && is_readable($migrationScriptPath)
                ) {
                    try {
                        include($migrationScriptPath);
                        $migrationDbModel->version = $currentVersion;
                        $migrationDbModel->save();
                    } catch (Exception $e) {
                        $this->migrationError(" [Ex] ERROR executing migration. - " . $e->getMessage());
                    } catch (Error $e) {
                        $this->migrationError(" [E] ERROR executing migration. - " . $e->getMessage());
                    }
                } else {
                    $this->migrationError('ERROR!!! - Migration file is not accessible.');

                    return ExitCode::DATAERR;
                }
                echo PHP_EOL;
                $this->printSeparator('*');
                echo PHP_EOL;
            }
        } else {
            $this->migrationError('ERROR!!! Cannot find business migration version.');
        }

        return ExitCode::OK;
    }
}

################################################################################
#                                End of file                                   #
################################################################################
