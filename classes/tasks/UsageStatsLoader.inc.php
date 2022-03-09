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
use PKP\db\DAORegistry;
use PKP\task\PKPUsageStatsLoader;

class UsageStatsLoader extends PKPUsageStatsLoader
{
    private $statsInstitutionDao;
    private $statsTotalDao;
    private $statsUniqueItemInvestigationsDao;
    private $statsUniqueItemRequestsDao;
    private $statsUniqueTitleInvestigationsDao;
    private $statsUniqueTitleRequestsDao;

    /**
     * Constructor.
     */
    public function __construct($args)
    {
        $this->statsInstitutionDao = DAORegistry::getDAO('UsageStatsInstitutionTemporaryRecordDAO'); /* @var UsageStatsInstitutionTemporaryRecordDAO $statsInstitutionDao */
        $this->statsTotalDao = DAORegistry::getDAO('UsageStatsTotalTemporaryRecordDAO'); /* @var UsageStatsTotalTemporaryRecordDAO $statsTotalDao */
        $this->statsUniqueItemInvestigationsDao = DAORegistry::getDAO('UsageStatsUniqueItemInvestigationsTemporaryRecordDAO'); /* @var UsageStatsUniqueItemInvestigationsTemporaryRecordDAO $statsUniqueItemInvestigationsDao */
        $this->statsUniqueItemRequestsDao = DAORegistry::getDAO('UsageStatsUniqueItemRequestsTemporaryRecordDAO'); /* @var UsageStatsUniqueItemRequestsTemporaryRecordDAO $statsUniqueItemRequestsDao */
        $this->statsUniqueTitleInvestigationsDao = DAORegistry::getDAO('UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO'); /* @var UsageStatsUniqueTitleInvestigationsTemporaryRecordDAO $statsUniqueTitleInvestigationsDao */
        $this->statsUniqueTitleRequestsDao = DAORegistry::getDAO('UsageStatsUniqueTitleRequestsTemporaryRecordDAO'); /* @var UsageStatsUniqueTitleRequestsTemporaryRecordDAO $statsUniqueTitleRequestsDao */
        parent::__construct($args);
    }

    protected function deleteByLoadId(string $loadId)
    {
        $this->statsInstitutionDao->deleteByLoadId($loadId);
        $this->statsTotalDao->deleteByLoadId($loadId);
        $this->statsUniqueItemInvestigationsDao->deleteByLoadId($loadId);
        $this->statsUniqueItemRequestsDao->deleteByLoadId($loadId);
        $this->statsUniqueTitleInvestigationsDao->deleteByLoadId($loadId);
        $this->statsUniqueTitleRequestsDao->deleteByLoadId($loadId);
    }

    protected function insertTemporaryUsageStatsData(object $entry, int $lineNumber, string $loadId)
    {
        $this->statsInstitutionDao->insert($entry->institutionIds, $lineNumber, $loadId);
        $this->statsTotalDao->insert($entry, $lineNumber, $loadId);
        if (!empty($entry->submissionId)) {
            $this->statsUniqueItemInvestigationsDao->insert($entry, $lineNumber, $loadId);
            $this->statsUniqueTitleInvestigationsDao->insert($entry, $lineNumber, $loadId);
            if ($entry->assocType == Application::ASSOC_TYPE_SUBMISSION_FILE) {
                $this->statsUniqueItemRequestsDao->insert($entry, $lineNumber, $loadId);
                $this->statsUniqueTitleRequestsDao->insert($entry, $lineNumber, $loadId);
            }
        }
    }

    protected function checkForeignKeys(object $entry): array
    {
        return $this->statsTotalDao->checkForeignKeys($entry);
    }

    /**
     * Validate an usage log entry.
     */
    protected function isLogEntryValid(object $entry)
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
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\tasks\UsageStatsLoader', '\UsageStatsLoader');
}
