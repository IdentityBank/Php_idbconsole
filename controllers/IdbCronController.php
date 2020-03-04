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

use Exception;
use idbyii2\helpers\IdbCron;
use yii\console\ExitCode;

################################################################################
# Class(es)                                                                    #
################################################################################

/**
 * Cron tools for IDB.
 *
 * Tools to execute by cron.
 *
 **/
class IdbCronController extends IdbControllerBase
{

    public $defaultAction = 'daily';

    public function setCronArgs($args)
    {
        if (!empty($args)) {
            $args = json_decode($args, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                if (!empty($args['quiet'])) {
                    $this->quiet = $args['quiet'];
                }
            }
        }
    }

    /**
     * Cron task - Daily
     *
     * All tasks which have to be executed every day.
     *
     **/
    public function actionDaily($args = null)
    {
        try {
            $this->setCronArgs($args);
            $this->printHeaderInfo('Daily');
            IdbCron::daily($args);
            $this->printFooterInfo('Daily');
        } catch (Exception $e) {
            var_dump($e->getMessage());

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Cron task - Daily Morning
     *
     * All tasks which have to be executed every day at the morning.
     *
     **/
    public function actionDailyMorning($args = null)
    {
        try {
            $this->setCronArgs($args);
            $this->printHeaderInfo('Daily Morning');
            IdbCron::dailyMorning($args);
            $this->printFooterInfo('Daily Morning');
        } catch (Exception $e) {
            var_dump($e->getMessage());

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Cron task - Hour
     *
     * All tasks which have to be executed every hour.
     *
     **/
    public function actionHour($args = null)
    {
        try {
            $this->setCronArgs($args);
            $this->printHeaderInfo('Hour');
            IdbCron::hour($args);
            $this->printFooterInfo('Hour');
        } catch (Exception $e) {
            var_dump($e->getMessage());

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Cron task - Half Hour
     *
     * All tasks which have to be executed every half hour.
     *
     **/
    public function actionHalfHour($args = null)
    {
        try {
            $this->setCronArgs($args);
            $this->printHeaderInfo('Half Hour');
            IdbCron::halfHour($args);
            $this->printFooterInfo('Half Hour');
        } catch (Exception $e) {
            var_dump($e->getMessage());

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Cron task - Quarter Hour
     *
     * All tasks which have to be executed every quarter hour.
     *
     **/
    public function actionQuarterHour($args = null)
    {
        try {
            $this->setCronArgs($args);
            $this->printHeaderInfo('Quarter Hour');
            IdbCron::quarterHour($args);
            $this->printFooterInfo('Quarter Hour');
        } catch (Exception $e) {
            var_dump($e->getMessage());

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * Cron task - Minute 5
     *
     * All tasks which have to be executed every 5 minutes.
     *
     **/
    public function actionMinute5($args = null)
    {
        try {
            $this->setCronArgs($args);
            $this->printHeaderInfo('Minute 5');
            IdbCron::minute5($args);
            $this->printFooterInfo('Minute 5');
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
