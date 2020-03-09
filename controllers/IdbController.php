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

use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\Console;

################################################################################
# Class(es)                                                                    #
################################################################################

/**
 * Manages IDB application actions.
 *
 * We allow to execute actions require to initialize and maintain IDB server
 * services.
 *
 **/
class IdbController extends Controller
{

    /**
     * @var string this is message to echo.
     */
    public $message;

    public $defaultAction = 'echo';

    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            [
                'message',
            ]
        );
    }

    public function optionAliases()
    {
        return array_merge(
            parent::optionAliases(),
            [
                'm' => 'message',
            ]
        );
    }

    public function getHelpSummary()
    {
        return parent::getHelpSummary();
    }

    /**
     * Displays available commands or the detailed information
     */
    public function actionUsage()
    {
        $this->stdout($this->getHelpSummary() . PHP_EOL);
        $helpCommand = Console::ansiFormat('idbconsole help idb', [Console::FG_CYAN]);
        $this->stdout("Use $helpCommand to get usage info." . PHP_EOL);

        return ExitCode::OK;
    }

    /**
     * Example action (ECHO test)
     *
     * Description of example action
     *
     * ```
     * idbconsole idb/echo "info text"
     * ```
     *
     * @param string $info info text to be printed in bold.
     *
     **/
    public function actionEcho($info)
    {
        if (!empty($info)) {
            $this->stdout($info . PHP_EOL, Console::BOLD);
        }
        if (!empty($this->message)) {
            echo $this->ansiFormat($this->message . PHP_EOL, Console::FG_YELLOW);
        }
    }
}

################################################################################
#                                End of file                                   #
################################################################################
