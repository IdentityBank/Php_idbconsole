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

use Aws\S3\PostObjectV4;
use Aws\S3\S3Client;
use Exception;
use yii\console\ExitCode;

################################################################################
# Class(es)                                                                    #
################################################################################

/**
 * IDB Storage tools for console.
 *
 * Tools to manage and check IDB storage.
 *
 **/
class IdbStorageController extends IdbControllerBase
{

    /**
     * Check Access to IDB storage
     *
     * Verify if storage is connected and accessible by IDB system.
     *
     **/
    public function actionCheckAccess($args = null)
    {
        try {
            $this->printHeaderInfo('IDB Storage - Access');
            $this->printFooterInfo('IDB Storage - Access');
        } catch (Exception $e) {
            var_dump($e->getMessage());

            return ExitCode::UNSPECIFIED_ERROR;
        }

        return ExitCode::OK;
    }

    /**
     * IDB AWS S3 Helper
     *
     * Helper to execute actions for AWS S3.
     *
     **/
    public function actionAwsS3Helper($args = null)
    {
        try {
            $args = json_decode($args, true);
            if (
                !empty($args['command'])
                && !empty($args['commandConfig'])
            ) {
                $this->quiet = empty($args['debug']) ? true : !$args['debug'];
                $this->printHeaderInfo('IDB Storage - AWS S3');
                switch ($args['command']) {
                    case 'PostObjectV4':
                        {
                            $commandConfigAwsS3 =
                            $commandConfigAwsS3Default = [
                                'credentials' => [
                                    'key' => '',
                                    'secret' => '',
                                ],
                                'bucket' => '',
                                'region' => 'eu-central-1',
                                'version' => 'latest',
                                'expires' => '+1 hours',
                                'acl' => 'bucket-owner-full-control',
                                'key' => '',
                                'prefix' => ''
                            ];
                            $commandConfigAwsS3 = $args['commandConfig'];
                            if (empty($commandConfigAwsS3['region'])) {
                                $commandConfigAwsS3['region'] = $commandConfigAwsS3Default['region'];
                            }
                            if (empty($commandConfigAwsS3['version'])) {
                                $commandConfigAwsS3['version'] = $commandConfigAwsS3Default['version'];
                            }
                            if (empty($commandConfigAwsS3['expires'])) {
                                $commandConfigAwsS3['expires'] = $commandConfigAwsS3Default['expires'];
                            }
                            if (empty($commandConfigAwsS3['acl'])) {
                                $commandConfigAwsS3['acl'] = $commandConfigAwsS3Default['acl'];
                            }

                            $bucket = $commandConfigAwsS3['bucket'];
                            $expires = $commandConfigAwsS3['expires'];

                            $configAwsS3 = [
                                'credentials' => [
                                    'key' => $commandConfigAwsS3['credentials']['key'],
                                    'secret' => $commandConfigAwsS3['credentials']['secret'],
                                ],
                                'bucket' => $commandConfigAwsS3['bucket'],
                                'region' => $commandConfigAwsS3['region'],
                                'version' => $commandConfigAwsS3['version'],
                            ];

                            $formInputs = [
                                'acl' => $commandConfigAwsS3['acl'],
                                'key' => $commandConfigAwsS3['key'],
                            ];

                            $options = [
                                ['bucket' => $bucket],
                                ['acl' => $commandConfigAwsS3['acl']],
                                ['starts-with', '$key', $commandConfigAwsS3['prefix']]
                            ];

                            $clientAwsS3 = new S3Client($configAwsS3);

                            $postObject = new PostObjectV4(
                                $clientAwsS3,
                                $bucket,
                                $formInputs,
                                $options,
                                $expires
                            );

                            $formAttributes = $postObject->getFormAttributes();
                            $formInputs = $postObject->getFormInputs();

                            $returnValue = json_encode(
                                [
                                    'formAttributes' => $formAttributes,
                                    'formInputs' => $formInputs
                                ]
                            );

                            $this->output($returnValue);
                        }
                        break;
                }
                $this->printFooterInfo('IDB Storage - AWS S3');
            }
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
