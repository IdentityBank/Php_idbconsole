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

use app\helpers\Translate;
use Exception;
use yii\console\ExitCode;

################################################################################
# Class(es)                                                                    #
################################################################################

/**
 * The script tools for IDB.
 *
 * External script tools for IDB environment.
 *
 **/
class IdbScriptController extends IdbControllerBase
{

    public $defaultAction = 'execute';

    /**
     * Execute script - Execute external script inside IDB environment
     *
     * Execute external script inside IDB environment which allow you to use config and models from Yii.
     *
     **/
    public function actionExecute($script_path)
    {
        try {
            $this->printHeaderInfo("Execute script");
            if (is_readable($script_path)) {
                $this->message(
                    Translate::_('console', "*  Execute script: [{script_path}]", ['script_path' => $script_path])
                );
                echo PHP_EOL;
                $this->printSeparator('*');
                echo PHP_EOL;
                include $script_path;
                echo PHP_EOL;
                $this->printSeparator('*');
                echo PHP_EOL;
            } else {
                $this->error(
                    Translate::_(
                        'console',
                        "* ERROR: The script: [{script_path}] does not exist!",
                        ['script_path' => $script_path]
                    )
                );
            }
            $this->printFooterInfo("Execute script");
        } catch (Exception $e) {
            var_dump($e->getMessage());

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }
}

################################################################################
#                                End of file                                   #
################################################################################
