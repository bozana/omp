<?php

/**
 * @file classes/statistics/UsageStatsUniqueItemInvestigationsTemporaryRecordDAO.inc.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsUniqueItemInvestigationsTemporaryRecordDAO
 * @ingroup statistics
 *
 * @brief Operations for retrieving and adding unique book and chapter item investigations.
 */

namespace APP\statistics;

use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;

class UsageStatsUniqueItemInvestigationsTemporaryRecordDAO
{
    /** This temporary table contains only book item usage (and no chapter usage) */
    public string $table = 'usage_stats_unique_item_investigations_temporary_records';

    /**
     * Add the passed usage statistic record.
     *
     * @param \stdClass $entryData [
     * 	chapter_id
     *  time
     *  ip
     *  canonicalUrl
     *  contextId
     *  submissionId
     *  representationId
     *  assocType
     *  assocId
     *  fileType
     *  userAgent
     *  country
     *  region
     *  city
     *  instituionIds
     * ]
     */
    public function insert(\stdClass $entryData, int $lineNumber, string $loadId)
    {
        DB::table($this->table)->insert([
            'date' => $entryData->time,
            'ip' => $entryData->ip,
            'user_agent' => substr($entryData->userAgent, 0, 255),
            'line_number' => $lineNumber,
            'context_id' => $entryData->contextId,
            'submission_id' => $entryData->submissionId,
            'chapter_id' => !empty($entryData->chapterId) ? $entryData->chapterId : null,
            'representation_id' => $entryData->representationId,
            'assoc_type' => $entryData->assocType,
            'assoc_id' => $entryData->assocId,
            'file_type' => $entryData->fileType,
            'country' => !empty($entryData->country) ? $entryData->country : '',
            'region' => !empty($entryData->region) ? $entryData->region : '',
            'city' => !empty($entryData->city) ? $entryData->city : '',
            'institution_ids' => json_encode($entryData->institutionIds),
            'load_id' => $loadId,
        ]);
    }

    /**
     * Delete all temporary records associated
     * with the passed load id.
     */
    public function deleteByLoadId(string $loadId)
    {
        DB::table($this->table)->where('load_id', '=', $loadId)->delete();
    }

    /**
     * Remove Unique Clicks
     * See https://www.projectcounter.org/code-of-practice-five-sections/7-processing-rules-underlying-counter-reporting-data/#counting
     */
    public function removeBookItemUniqueClicks()
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement("DELETE FROM {$this->table} usui WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usuit WHERE usuit.load_id = usui.load_id AND usuit.ip = usui.ip AND usuit.user_agent = usui.user_agent AND usuit.context_id = usui.context_id AND usuit.submission_id = usui.submission_id AND usuit.chapter_id IS NULL AND usui.chapter_id IS NULL AND EXTRACT(HOUR FROM usuit.date) = EXTRACT(HOUR FROM usui.date) AND usui.line_number < usuit.line_number) AS tmp)");
        } else {
            DB::statement("DELETE FROM {$this->table} usui WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usuit WHERE usuit.load_id = usui.load_id AND usuit.ip = usui.ip AND usuit.user_agent = usui.user_agent AND usuit.context_id = usui.context_id AND usuit.submission_id = usui.submission_id AND usuit.chapter_id IS NULL AND usui.chapter_id IS NULL AND TIMESTAMPDIFF(HOUR, usui.date, usuit.date) = 0 AND usui.line_number < usuit.line_number) AS tmp)");
        }
    }
    public function removeChapterItemUniqueClicks()
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement("DELETE FROM {$this->table} usui WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usuit WHERE usuit.load_id = usui.load_id AND usuit.ip = usui.ip AND usuit.user_agent = usui.user_agent AND usuit.context_id = usui.context_id AND usuit.submission_id = usui.submission_id AND usuit.chapter_id = usui.chapter_id AND usuit.chapter_id IS NOT NULL AND EXTRACT(HOUR FROM usuit.date) = EXTRACT(HOUR FROM usui.date) AND usui.line_number < usuit.line_number) AS tmp)");
        } else {
            DB::statement("DELETE FROM {$this->table} usui WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usuit WHERE usuit.load_id = usui.load_id AND usuit.ip = usui.ip AND usuit.user_agent = usui.user_agent AND usuit.context_id = usui.context_id AND usuit.submission_id = usui.submission_id AND usuit.chapter_id = usui.chapter_id AND usuit.chapter_id IS NOT NULL AND TIMESTAMPDIFF(HOUR, usui.date, usuit.date) = 0 AND usui.line_number < usuit.line_number) AS tmp)");
        }
    }

    public function loadMetricsCounterSubmissionDaily(string $loadId)
    {
        // construct metric_book_investigations_unique upsert
        $metricBookInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, count(*) as metric, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_book_investigations_unique = excluded.metric_book_investigations_unique;
                ';
        } else {
            $metricBookInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_investigations_unique = metric;
                ';
        }
        // load metric_book_investigations_unique
        DB::statement($metricBookInvestigationsUniqueUpsertSql, [$loadId]);

        // construct metric_chapter_investigations_unique upsert
        $metricChapterInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, count(*) as metric, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table}
                WHERE load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NOT NULL
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_chapter_investigations_unique = excluded.metric_chapter_investigations_unique;
                ';
        } else {
            $metricChapterInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_investigations_unique = metric;
                ';
        }
        // load metric_chapter_investigations_unique
        DB::statement($metricChapterInvestigationsUniqueUpsertSql, [$loadId]);
    }

    public function loadMetricsCounterSubmissionInstitutionDaily(string $loadId)
    {
        // construct metric_book_investigations_unique upsert
        $metricBookInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT usui.load_id, usui.context_id, usui.submission_id, DATE(usui.date) as date, usi.institution_id, 0 as metric_book_investigations, count(*) as metric, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table} usui
                JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usui.load_id AND usi.line_number = usui.line_number)
                WHERE usui.load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NULL AND usi.institution_id = ?
                GROUP BY usui.load_id, usui.context_id, usui.submission_id, DATE(usui.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricBookInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_book_investigations_unique = excluded.metric_book_investigations_unique;
                ';
        } else {
            $metricBookInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_book_investigations_unique = metric;
                ';
        }

        // construct metric_chapter_investigations_unique upsert
        $metricChapterInvestigationsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT usuci.load_id, usuci.context_id, usuci.submission_id, DATE(usuci.date) as date, usi.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, count(*) as metric, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, 0 as metric_title_requests_unique
                FROM {$this->table} usuci
                JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usuci.load_id AND usi.line_number = usuci.line_number)
                WHERE usuci.load_id = ? AND submission_id IS NOT NULL AND chapter_id IS NOT NULL AND usi.institution_id = ?
                GROUP BY usuci.load_id, usuci.context_id, usuci.submission_id, DATE(usuci.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricChapterInvestigationsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_chapter_investigations_unique = excluded.metric_chapter_investigations_unique;
                ';
        } else {
            $metricChapterInvestigationsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_chapter_investigations_unique = metric;
                ';
        }

        $statsInstitutionDao = DAORegistry::getDAO('UsageStatsInstitutionTemporaryRecordDAO'); /* @var $statsInstitutionDao UsageStatsInstitutionTemporaryRecordDAO */
        $institutionIds = $statsInstitutionDao->getInstitutionIdsByLoadId($loadId);
        foreach ($institutionIds as $institutionId) {
            // load metric_book_investigations_unique
            DB::statement($metricBookInvestigationsUniqueUpsertSql, [$loadId, (int) $institutionId]);
            // load metric_chapter_investigations_unique
            DB::statement($metricChapterInvestigationsUniqueUpsertSql, [$loadId, (int) $institutionId]);
        }
    }
}
