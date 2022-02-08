<?php

/**
 * @file classes/statistics/UsageStatsUniqueTitleRequestsTemporaryRecordDAO.inc.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsUniqueTitleRequestsTemporaryRecordDAO
 * @ingroup statistics
 *
 * @brief Operations for retrieving and adding unique title (i.e. submission) requests.
 */

namespace APP\statistics;

use APP\core\Application;
use Illuminate\Support\Facades\DB;
use PKP\config\Config;
use PKP\db\DAORegistry;

class UsageStatsUniqueTitleRequestsTemporaryRecordDAO
{
    /** This temporary table contains all (book and chapter) requests */
    public string $table = 'usage_stats_unique_title_requests_temporary_records';

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
    public function removeTitleUniqueClicks()
    {
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            DB::statement("DELETE FROM {$this->table} usutr WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usutrt WHERE usutrt.load_id = usutr.load_id AND usutrt.ip = usutr.ip AND usutrt.user_agent = usutr.user_agent AND usutrt.context_id = usutr.context_id AND usutrt.submission_id = usutr.submission_id AND EXTRACT(HOUR FROM usutrt.date) = EXTRACT(HOUR FROM usutr.date) AND usutr.line_number < usutrt.line_number) AS tmp)");
        } else {
            DB::statement("DELETE FROM {$this->table} usutr WHERE EXISTS (SELECT * FROM (SELECT 1 FROM {$this->table} usutrt WHERE usutrt.load_id = usutr.load_id AND usutrt.ip = usutr.ip AND usutrt.user_agent = usutr.user_agent AND usutrt.context_id = usutr.context_id AND usutrt.submission_id = usutr.submission_id AND TIMESTAMPDIFF(HOUR, usutr.date, usutrt.date) = 0 AND usutr.line_number < usutrt.line_number) AS tmp)");
        }
    }

    public function loadMetricsCounterSubmissionDaily(string $loadId)
    {
        // construct metric_title_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricTitleRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_daily (load_id, context_id, submission_id, date, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, count(*) as metric
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ?
                GROUP BY load_id, context_id, submission_id, DATE(date)) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_submission_daily_uc_load_id_context_id_submission_id_date DO UPDATE
                SET metric_title_requests_unique = excluded.metric_title_requests_unique;
                ';
        } else {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_title_requests_unique = metric;
                ';
        }
        // load metric_title_requests_unique
        DB::statement($metricTitleRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]);
    }

    public function loadMetricsCounterSubmissionGeoDaily(string $loadId)
    {
        // construct metric_title_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricTitleRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_geo_daily (load_id, context_id, submission_id, date, country, region, city, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (SELECT load_id, context_id, submission_id, DATE(date) as date, country, region, city, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, count(*) as metric
                FROM {$this->table}
                WHERE load_id = ? AND assoc_type = ?
                GROUP BY load_id, context_id, submission_id, DATE(date), country, region, city) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_geo_daily_uc_load_id_context_id_submission_id_country_region_city_date DO UPDATE
                SET metric_title_requests_unique = excluded.metric_title_requests_unique;
                ';
        } else {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_title_requests_unique = metric;
                ';
        }
        // load metric_title_requests_unique
        DB::statement($metricTitleRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE]);
    }

    public function loadMetricsCounterSubmissionInstitutionDaily(string $loadId)
    {
        // construct metric_title_requests_unique upsert
        // assoc_type should always be Application::ASSOC_TYPE_SUBMISSION_FILE, but include the condition however
        $metricTitleRequestsUniqueUpsertSql = "
            INSERT INTO metrics_counter_submission_institution_daily (load_id, context_id, submission_id, date, institution_id, metric_book_investigations, metric_book_investigations_unique, metric_book_requests, metric_book_requests_unique, metric_chapter_investigations, metric_chapter_investigations_unique, metric_chapter_requests, metric_chapter_requests_unique, metric_title_investigations_unique, metric_title_requests_unique)
            SELECT * FROM (
                SELECT usutr.load_id, usutr.context_id, usutr.submission_id, DATE(usutr.date) as date, usi.institution_id, 0 as metric_book_investigations, 0 as metric_book_investigations_unique, 0 as metric_book_requests, 0 as metric_book_requests_unique, 0 as metric_chapter_investigations, 0 as metric_chapter_investigations_unique, 0 as metric_chapter_requests, 0 as metric_chapter_requests_unique, 0 as metric_title_investigations_unique, count(*) as metric
                FROM {$this->table} usutr
                JOIN usage_stats_institution_temporary_records usi on (usi.load_id = usutr.load_id AND usi.line_number = usutr.line_number)
                WHERE usutr.load_id = ? AND usutr.assoc_type = ? AND usi.institution_id = ?
                GROUP BY usutr.load_id, usutr.context_id, usutr.submission_id, DATE(usutr.date), usi.institution_id) AS t
            ";
        if (substr(Config::getVar('database', 'driver'), 0, strlen('postgres')) === 'postgres') {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON CONFLICT ON CONSTRAINT metrics_institution_daily_uc_load_id_context_id_submission_id_institution_id_date DO UPDATE
                SET metric_title_requests_unique = excluded.metric_title_requests_unique;
                ';
        } else {
            $metricTitleRequestsUniqueUpsertSql .= '
                ON DUPLICATE KEY UPDATE metric_title_requests_unique = metric;
                ';
        }

        $statsInstitutionDao = DAORegistry::getDAO('UsageStatsInstitutionTemporaryRecordDAO'); /* @var $statsInstitutionDao UsageStatsInstitutionTemporaryRecordDAO */
        $institutionIds = $statsInstitutionDao->getInstitutionIdsByLoadId($loadId);
        foreach ($institutionIds as $institutionId) {
            // load metric_title_requests_unique
            DB::statement($metricTitleRequestsUniqueUpsertSql, [$loadId, Application::ASSOC_TYPE_SUBMISSION_FILE, (int) $institutionId]);
        }
    }
}
