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
use idbyii2\helpers\IdbMessagesHelper;
use Yii;
use yii\console\ExitCode;

################################################################################
# Class(es)                                                                    #
################################################################################

/**
 * Tools for messages for IDB portals.
 *
 * Tools used to manage messages used at IDB.
 *
 **/
class IdbMessageController extends IdbControllerBase
{

    public $defaultAction = 'list-language-messages';
    private $defaultProjectsPath = '/usr/local/share/p57b/php';

    /**
     * List all messages sections and locations
     *
     * @param bool        $onlySourceLanguage
     *
     * @param string|null $projectsPath
     */
    public function actionListLanguageMessages(bool $onlySourceLanguage = false, string $projectsPath = null)
    {
        $sourceLanguage = Yii::$app->sourceLanguage;
        if (
            empty($projectsPath)
            || ($projectsPath === 'null')
            || (!is_dir($projectsPath))
        ) {
            $projectsPath = $this->defaultProjectsPath;
        }
        if (!empty($dir) && substr($dir, -1) !== DIRECTORY_SEPARATOR) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        $this->printHeaderInfo('List language messages assets');
        $this->message("Project path: [$projectsPath]");
        $this->message("Source language: [$sourceLanguage]");
        if ($onlySourceLanguage) {
            $languageMessagesFiles = IdbMessagesHelper::getAllMessagesLocations($projectsPath, $sourceLanguage);
        } else {
            $languageMessagesFiles = IdbMessagesHelper::getAllMessagesLocations($projectsPath);
        }
        foreach ($languageMessagesFiles as $languageMessagesFileCategory => $languageCategoryFiles) {
            $this->printSeparator();
            $this->message($languageMessagesFileCategory);
            $this->printSeparator();
            foreach ($languageCategoryFiles as $languageCategoryFile) {
                $this->message($languageCategoryFile);
            }
            $this->printSeparator();
        }
        $this->printFooterInfo('List language messages assets');
    }

    /**
     * Lists all source language keys for translation
     *
     * @param string|null $projectsPath
     * @param int         $displayLimit
     * @param array|null  $categories
     */
    public function actionListMessageKeys(string $projectsPath = null, int $displayLimit = -1, array $categories = null)
    {
        $sourceLanguage = Yii::$app->sourceLanguage;
        if (
            empty($projectsPath)
            || ($projectsPath === 'null')
            || (!is_dir($projectsPath))
        ) {
            $projectsPath = $this->defaultProjectsPath;
        }
        if (!empty($dir) && substr($dir, -1) !== DIRECTORY_SEPARATOR) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        $this->printHeaderInfo('List message keys');
        $this->message("Project path: [$projectsPath]");
        $this->message("Source language: [$sourceLanguage]");
        $languageMessageKeys = IdbMessagesHelper::getAllMessageKeys($projectsPath, $sourceLanguage, $categories);
        $languageMessageKeysCount = count($languageMessageKeys);
        $this->printSeparator();
        foreach ($languageMessageKeys as $languageMessageKey) {
            $this->message($languageMessageKey);
            if ($displayLimit > 0) {
                $displayLimit--;
            }
            if ($displayLimit == 0) {
                break;
            }
        }
        $this->printSeparator();
        $this->message("We have $languageMessageKeysCount key messages.");
        $this->printSeparator();
        $this->printFooterInfo('List message keys');
    }

    /**
     * Restore source language messages
     *
     * Allow to use source language translations and replace original text.
     *
     * @param $configFile - the path or alias of the configuration file.
     *
     * @return int - return execution status
     */
    public function actionRestoreSourceLanguageMessages(string $configFile)
    {
        try {
            if (
                is_file($configFile)
                && is_readable($configFile)
            ) {
                $sourceLanguage = Yii::$app->sourceLanguage;
                $portalPath = realpath(dirname(realpath($configFile)) . DIRECTORY_SEPARATOR . "..");
                $i18n = include($configFile);
                $translator = ($i18n['translator']);
                $translations = array_keys(Yii::$app->i18n->translations);
                $translationPath = $portalPath . DIRECTORY_SEPARATOR . 'messages' . DIRECTORY_SEPARATOR
                    . $sourceLanguage . DIRECTORY_SEPARATOR;
                $translationsFiles = IdbMessagesHelper::getAllProjectMessages(
                    $portalPath,
                    $sourceLanguage
                );
                $this->printHeaderInfo('Restore source language messages');
                $this->message("Portal path: [$portalPath]");
                $this->message("Source language: [$sourceLanguage]");
                $this->message("Translator: [$translator]");
                $files = IdbMessagesHelper::scanFiles($portalPath);
                $filesCount = count($files);
                $this->message("Number of files: [$filesCount]");
                $excludes = IdbMessagesHelper::loadExcludes($portalPath, $sourceLanguage);
                $excludesCount = count($excludes);
                $this->message("Keys to exclude from restore: [$excludesCount]");
                $updateStatus = IdbMessagesHelper::executeOriginalTranslationFiles(
                    $translationsFiles,
                    $files,
                    $excludes,
                    $translator
                );
                foreach ($updateStatus as $translationsFile => $updateCounter) {
                    $this->printSeparator();
                    $this->message($translationsFile);
                    $this->message("Updated: [$updateCounter]");
                    $this->printSeparator();
                }
                $this->printFooterInfo('Restore source language messages');
            } else {
                $this->error("Cannot read config file: [$configFile]");

                return ExitCode::DATAERR;
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
