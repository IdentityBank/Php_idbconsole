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

use idbyii2\models\db\BusinessDatabaseData;
use idbyii2\models\idb\IdbBankClientBusiness;
use yii\console\ExitCode;

################################################################################
# Migration                                                                    #
################################################################################

/**
 * Create all business event table
 */

$version = 1;
$migrationName = "IDB Pseudonymisation";
$this->migrationHeader($migrationName, $version);
$index = -1;
$start = microtime(true);
$databases = BusinessDatabaseData::find()->all();

foreach ($databases as $index => $database) {

    try {
        $businessId = $database->business_db_id;

        echo 'Create: ' . $businessId . PHP_EOL;
        echo 'New table: ' . $database->idb_data_id . '.pn' . PHP_EOL;

        $clientModel = IdbBankClientBusiness::model($database->business_db_id);

        $response = $clientModel->recreateAccountPseudonymisation($database->idb_data_id);
        var_dump($response);

        if ($response == 457) { // we already have that table created
            echo 'Table ' . $database->idb_data_id . '.pn' . ' already exist!' . PHP_EOL;
        } elseif (
            empty($response['Query'])
            || !is_numeric($response['Query'])
        ) {
            $response = json_encode($response);
            $this->migrationError("IDB API Error: [$response]");

            return ExitCode::UNSPECIFIED_ERROR;
        }
    } catch (Exception $e) {
        $this->migrationError($e->getMessage());

        return ExitCode::UNSPECIFIED_ERROR;
    }

}

$index++;
$this->migrationFooter($migrationName, (microtime(true) - $start), ["Migrated $index table(s)"]);

################################################################################
#                                End of file                                   #
################################################################################
