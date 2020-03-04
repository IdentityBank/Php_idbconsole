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

use idbyii2\models\db\BusinessAccount;
use idbyii2\models\db\BusinessAccountUser;
use idbyii2\models\idb\BusinessIdbBillingClient;

################################################################################
# Migration                                                                    #
################################################################################

$model = BusinessIdbBillingClient::model();
$businessPackages = $model->findAllBusinessPackages();

foreach ($businessPackages as $businessPackage) {
    $accountUser = BusinessAccountUser::find()->where(['uid' => $businessPackage[1]])->one();
    if (!empty($accountUser)) {
        $this->message("Update for account user: Account ID: [$accountUser->aid]");
        $idbAccount = BusinessAccount::find()->where(['aid' => $accountUser->aid])->one();
        if(!empty($idbAccount)) {
            $this->message("Update for business ID: $idbAccount->oid");
            $model->updateBusinessPackage($businessPackage[0], ['business_id' => $idbAccount->oid]);
        }
    }
}

################################################################################
#                                End of file                                   #
################################################################################
