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

use idbyii2\helpers\Localization;
use idbyii2\helpers\Translate;
use yii\console\Controller;
use yii\helpers\Console;

################################################################################
# Class(es)                                                                    #
################################################################################

class IdbControllerBase extends Controller
{

    protected $quiet = false;

    public function getHelpSummary()
    {
        return parent::getHelpSummary();
    }

    public function printSeparator($char = '-', $color = [Console::FG_GREY, Console::BOLD], $multiplier = 80)
    {
        $separator = str_repeat($char, $multiplier) . PHP_EOL;
        echo Console::ansiFormat($separator, $color);
    }

    public function error($message)
    {
        if ($this->quiet) {
            return;
        }
        if (!empty($message)) {
            echo Console::ansiFormat($message . PHP_EOL, [Console::FG_RED, Console::BOLD]);
        }
    }

    public function warning($message)
    {
        if ($this->quiet) {
            return;
        }
        if (!empty($message)) {
            echo Console::ansiFormat($message . PHP_EOL, [Console::FG_YELLOW, Console::BOLD]);
        }
    }

    public function info($message)
    {
        if ($this->quiet) {
            return;
        }
        if (!empty($message)) {
            echo Console::ansiFormat($message . PHP_EOL, [Console::FG_GREEN, Console::BOLD]);
        }
    }

    public function message(
        $message,
        $force = false
    ) {
        if (
            !$force
            && $this->quiet
        ) {
            return;
        }
        if (!empty($message)) {
            echo Console::ansiFormat($message . PHP_EOL, [Console::FG_GREY, Console::BOLD]);
        }
    }

    public function output(
        $message,
        $force = true
    ) {
        if (
            !$force
            && $this->quiet
        ) {
            return;
        }
        if (!empty($message)) {
            echo $message . PHP_EOL;
        }
    }

    public function printHeaderInfo($info)
    {
        if ($this->quiet) {
            return;
        }
        echo PHP_EOL;
        $this->printSeparator();
        if (!empty($info)) {
            echo Console::ansiFormat(
                Translate::_('console', 'Execute action (Start): ') . $info . PHP_EOL,
                [Console::FG_YELLOW, Console::BOLD]
            );
        }
        $this->printSeparator();
        echo Console::ansiFormat(Localization::getDateTimeLogString() . PHP_EOL, [Console::FG_GREEN, Console::BOLD]);
        $this->printSeparator();
        echo PHP_EOL;
    }

    public function printFooterInfo($info)
    {
        if ($this->quiet) {
            return;
        }
        echo PHP_EOL;
        $this->printSeparator();
        echo Console::ansiFormat(Localization::getDateTimeLogString() . PHP_EOL, [Console::FG_RED, Console::BOLD]);
        $this->printSeparator();
        if (!empty($info)) {
            echo Console::ansiFormat(
                Translate::_('console', 'Action (End): ') . $info . PHP_EOL,
                [Console::FG_YELLOW, Console::BOLD]
            );
        }
        $this->printSeparator();
        echo PHP_EOL;
    }
}

################################################################################
#                                End of file                                   #
################################################################################
