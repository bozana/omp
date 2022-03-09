<?php

/**
 * @file classes/tasks/UsageStatsLoader.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsLoader
 * @ingroup tasks
 *
 * @brief Scheduled task to extract transform and load usage statistics data into database.
 */

namespace APP\tasks;

use APP\core\Application;
use APP\statistics\StatisticsHelper;
use PKP\core\Core;
use PKP\db\DAORegistry;
use PKP\scheduledTask\ScheduledTaskHelper;
use PKP\task\FileLoader;
use PKP\task\PKPUsageStatsLoader;

class UsageStatsLoader extends PKPUsageStatsLoader
{
    /**
     * @copydoc FileLoader::processFile()
     * The file's entries MUST be ordered by date-time to successfully identify double-clicks and unique items.
     */
    protected function processFile(string $filePath)
    {
        $fhandle = fopen($filePath, 'r');
        if (!$fhandle) {
            // TO-DO: move plugins.generic.usageStats.openFileFailed to usageStats.openFileFailed
            throw new \Exception(__('usageStats.openFileFailed', ['file' => $filePath]));
        }

        $loadId = basename($filePath);

        $statsInstitutionDao = DAORegistry::getDAO('UsageStatsInstitutionTemporaryRecordDAO'); /* @var $statsInstitutionDao UsageStatsInstitutionTemporaryRecordDAO */
        $statsTotalDao = DAORegistry::getDAO('UsageStatsTotalTemporaryRecordDAO'); /* @var $statsTotalDao UsageStatsTotalTemporaryRecordDAO */
        $statsUniqueItemInvestigationsDao = DAORegistry::getDAO('UsageStatsUniqueItemInvestigationsTemporaryRecordDAO'); /* @var $statsUniqueItemInvestigationsDao UsageStatsUniqueItemInvestigationsTemporaryRecordDAO */
        $statsUniqueItemRequestsDao = DAORegistry::getDAO('UsageStatsUniqueItemRequestsTemporaryRecordDAO'); /* @var $statsUniqueItemRequestsDao UsageStatsUniqueItemRequestsTemporaryRecordDAO */
        $statsUniqueTitleInvestigationsDao = DAORegistry::getDAO('UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO'); /* @var $statsUniqueTitleInvestigationsDao UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO */
        $statsUniqueTitleRequestsDao = DAORegistry::getDAO('UsageStatsUniqueTitleRequestsTemporaryRecordDAO'); /* @var $statsUniqueTitleRequestsDao UsageStatsUniqueTitleRequestsTemporaryRecordDAO */

        // Make sure we don't have any temporary records associated
        // with the current load id in database.
        $statsInstitutionDao->deleteByLoadId($loadId);
        $statsTotalDao->deleteByLoadId($loadId);
        $statsUniqueItemInvestigationsDao->deleteByLoadId($loadId);
        $statsUniqueItemRequestsDao->deleteByLoadId($loadId);
        $statsUniqueTitleInvestigationsDao->deleteByLoadId($loadId);
        $statsUniqueTitleRequestsDao->deleteByLoadId($loadId);

        $lineNumber = 0;
        while (!feof($fhandle)) {
            $lineNumber++;
            $line = trim(fgets($fhandle));
            if (empty($line) || substr($line, 0, 1) === '#') {
                continue;
            } // Spacing or comment lines.

            $entryData = json_decode($line);

            try {
                $this->_isLogEntryValid($entryData);
            } catch (\Exception $e) {
                $file = 'debug.txt';
                $current = file_get_contents($file);
                $current .= print_r("++++ error ++++\n", true);
                $current .= print_r($e->getMessage(), true);
                $current .= print_r("++++ lineNumber ++++\n", true);
                $current .= print_r($lineNumber, true);
                file_put_contents($file, $current);

                throw new \Exception(__(
                    'usageStats.invalidLogEntry',
                    ['file' => $filePath, 'lineNumber' => $lineNumber, 'error' => $e->getMessage()]
                ));
            }

            // Avoid bots.
            if (Core::isUserAgentBot($entryData->userAgent)) {
                continue;
            }

            $foreignKeyErrors = $statsTotalDao->checkForeignKeys($entryData);
            if (!empty($foreignKeyErrors)) {
                $missingForeignKeys = implode(', ', $foreignKeyErrors);
                $this->addExecutionLogEntry(__('usageStats.logfileProcessing.foreignKeyError', ['missingForeignKeys' => $missingForeignKeys, 'loadId' => $loadId, 'lineNumber' => $lineNumber]), ScheduledTaskHelper::SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
                $file = 'debug.txt';
                $current = file_get_contents($file);
                $current .= print_r("++++ missingForeignKeys ++++\n", true);
                $current .= print_r($missingForeignKeys, true);
                $current .= print_r("++++ loadId ++++\n", true);
                $current .= print_r($loadId, true);
                $current .= print_r("++++ lineNumber ++++\n", true);
                $current .= print_r($lineNumber, true);
                file_put_contents($file, $current);
            } else {
                $statsInstitutionDao->insert($entryData->institutionIds, $lineNumber, $loadId);
                $statsTotalDao->insert($entryData, $lineNumber, $loadId);
                if (!empty($entryData->submissionId)) {
                    $statsUniqueItemInvestigationsDao->insert($entryData, $lineNumber, $loadId);
                    $statsUniqueTitleInvestigationsDao->insert($entryData, $lineNumber, $loadId);
                    if ($entryData->assocType == Application::ASSOC_TYPE_SUBMISSION_FILE) {
                        $statsUniqueItemRequestsDao->insert($entryData, $lineNumber, $loadId);
                        $statsUniqueTitleRequestsDao->insert($entryData, $lineNumber, $loadId);
                    }
                }
            }
        }
        fclose($fhandle);

        //$start = microtime(true);

        $statsTotalDao->removeDoubleClicks(self::COUNTER_DOUBLE_CLICK_TIME_FILTER_SECONDS);
        $statsUniqueItemInvestigationsDao->removeBookItemUniqueClicks();
        $statsUniqueItemInvestigationsDao->removeChapterItemUniqueClicks();
        $statsUniqueItemRequestsDao->removeBookItemUniqueClicks();
        $statsUniqueItemRequestsDao->removeChapterItemUniqueClicks();
        $statsUniqueTitleInvestigationsDao->removeTitleUniqueClicks();
        $statsUniqueTitleRequestsDao->removeTitleUniqueClicks();

        // load total and item metrics from temporary tables into the actual metrics tables
        $loadSuccessful = $this->_loadData($loadId);

        // TO-DO: remove comments
        //$statsTotalDao->deleteByLoadId($loadId);
        //$statsUniqueItemInvestigationsDao->deleteByLoadId($loadId);
        //$statsUniqueItemRequestsDao->deleteByLoadId($loadId);
        //$statsUniqueTitleInvestigationsDao->deleteByLoadId($loadId);
        //$statsUniqueTitleRequestsDao->deleteByLoadId($loadId);
        //$statsInstitutionDao->deleteByLoadId($loadId);

        if (!$loadSuccessful) {
            // TO-DO: move plugins.generic.usageStats.loadDataError to usageStats.loadDataError
            $this->addExecutionLogEntry(__(
                'usageStats.loadDataError',
                ['file' => $filePath]
            ), ScheduledTaskHelper::SCHEDULED_TASK_MESSAGE_TYPE_ERROR);
            return FileLoader::FILE_LOADER_RETURN_TO_STAGING;
        } else {
            return true;
        }

        return true;
    }

    /**
     * Validate an usage log entry.
     */
    protected function _isLogEntryValid(\stdClass $entry)
    {
        if (!$this->_validateDate($entry->time)) {
            throw new \Exception(__('usageStats.invalidLogEntry.time'));
        }
        // check hashed IP ?
        // check canonicalUrl ?
        if (!is_int($entry->contextId)) {
            throw new \Exception(__('usageStats.invalidLogEntry.contextId'));
        } else {
            if ($entry->assocType == Application::ASSOC_TYPE_PRESS && $entry->assocId != $entry->contextId) {
                throw new \Exception(__('usageStats.invalidLogEntry.contextAssocTypeNoMatch'));
            }
        }
        if (!empty($entry->submissionId)) {
            if (!is_int($entry->submissionId)) {
                throw new \Exception(__('usageStats.invalidLogEntry.submissionId'));
            } else {
                if ($entry->assocType == Application::ASSOC_TYPE_SUBMISSION && $entry->assocId != $entry->submissionId) {
                    throw new \Exception(__('usageStats.invalidLogEntry.submissionAssocTypeNoMatch'));
                }
            }
        }
        if (!empty($entry->chapterId)) {
            if (!is_int($entry->chapterId)) {
                throw new \Exception(__('usageStats.invalidLogEntry.chapterId'));
            }
        }
        if (!empty($entry->representationId) && !is_int($entry->representationId)) {
            throw new \Exception(__('usageStats.invalidLogEntry.representationId'));
        }
        $validAssocTypes = [
            Application::ASSOC_TYPE_SUBMISSION_FILE,
            Application::ASSOC_TYPE_SUBMISSION_FILE_COUNTER_OTHER,
            Application::ASSOC_TYPE_CHAPTER,
            Application::ASSOC_TYPE_SUBMISSION,
            Application::ASSOC_TYPE_SERIES,
            Application::ASSOC_TYPE_PRESS,
        ];
        if (!in_array($entry->assocType, $validAssocTypes)) {
            throw new \Exception(__('usageStats.invalidLogEntry.assocType'));
        }
        if (!is_int($entry->assocId)) {
            throw new \Exception(__('usageStats.invalidLogEntry.assocId'));
        }
        $validFileTypes = [
            StatisticsHelper::STATISTICS_FILE_TYPE_PDF,
            StatisticsHelper::STATISTICS_FILE_TYPE_DOC,
            StatisticsHelper::STATISTICS_FILE_TYPE_HTML,
            StatisticsHelper::STATISTICS_FILE_TYPE_OTHER,
        ];
        if (!empty($entry->fileType) && !in_array($entry->fileType, $validFileTypes)) {
            throw new \Exception(__('usageStats.invalidLogEntry.fileType'));
        }
        if (!empty($entry->country) && (!ctype_alpha($entry->country) || !(strlen($entry->country) == 2))) {
            throw new \Exception(__('usageStats.invalidLogEntry.country'));
        }
        if (!empty($entry->region) && (!ctype_alnum($entry->region) || !(strlen($entry->region) <= 3))) {
            throw new \Exception(__('usageStats.invalidLogEntry.region'));
        }
        if (!is_array($entry->institutionIds)) {
            throw new \Exception(__('usageStats.invalidLogEntry.institutionIds'));
        }
    }

    /**
     * Load the entries inside the temporary database associated with
     * the passed load id to the metrics tables.
     */
    private function _loadData(string $loadId): bool
    {
        $statsTotalDao = DAORegistry::getDAO('UsageStatsTotalTemporaryRecordDAO'); /* @var $statsTotalDao UsageStatsTotalTemporaryRecordDAO */
        $statsUniqueItemInvestigationsDao = DAORegistry::getDAO('UsageStatsUniqueItemInvestigationsTemporaryRecordDAO'); /* @var $statsUniqueItemInvestigationsDao UsageStatsUniqueItemInvestigationsTemporaryRecordDAO */
        $statsUniqueItemRequestsDao = DAORegistry::getDAO('UsageStatsUniqueItemRequestsTemporaryRecordDAO'); /* @var $statsUniqueItemRequestsDao UsageStatsUniqueItemRequestsTemporaryRecordDAO */
        $statsUniqueTitleInvestigationsDao = DAORegistry::getDAO('UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO'); /* @var $statsUniqueTitleInvestigationsDao UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO */
        $statsUniqueTitleRequestsDao = DAORegistry::getDAO('UsageStatsUniqueTitleRequestsTemporaryRecordDAO'); /* @var $statsUniqueTitleRequestsDao UsageStatsUniqueTitleRequestsTemporaryRecordDAO */

        $statsTotalDao->loadMetricsContext($loadId);
        $statsTotalDao->loadMetricsSeries($loadId);
        $statsTotalDao->loadMetricsSubmission($loadId);

        // Geo database only contains total and unique views, no requests
        $statsTotalDao->deleteSubmissionGeoDailyByLoadId($loadId); // always call first, before loading the data
        $statsTotalDao->loadMetricsSubmissionGeoDaily($loadId);
        $statsUniqueTitleInvestigationsDao->loadMetricsSubmissionGeoDaily($loadId);

        // metrics_counter_submission_daily
        $statsTotalDao->deleteCounterSubmissionDailyByLoadId($loadId); // always call first, before loading the data
        $statsTotalDao->loadMetricsCounterSubmissionDaily($loadId);
        $statsUniqueItemInvestigationsDao->loadMetricsCounterSubmissionDaily($loadId);
        $statsUniqueItemRequestsDao->loadMetricsCounterSubmissionDaily($loadId);
        $statsUniqueTitleInvestigationsDao->loadMetricsCounterSubmissionDaily($loadId);
        $statsUniqueTitleRequestsDao->loadMetricsCounterSubmissionDaily($loadId);

        // metrics_counter_submission_institution_daily
        $statsTotalDao->deleteCounterSubmissionInstitutionDailyByLoadId($loadId); // always call first, before loading the data
        $statsTotalDao->loadMetricsCounterSubmissionInstitutionDaily($loadId);
        $statsUniqueItemInvestigationsDao->loadMetricsCounterSubmissionInstitutionDaily($loadId);
        $statsUniqueItemRequestsDao->loadMetricsCounterSubmissionInstitutionDaily($loadId);
        $statsUniqueTitleInvestigationsDao->loadMetricsCounterSubmissionInstitutionDaily($loadId);
        $statsUniqueTitleRequestsDao->loadMetricsCounterSubmissionInstitutionDaily($loadId);

        return true;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\tasks\UsageStatsLoader', '\UsageStatsLoader');
}
