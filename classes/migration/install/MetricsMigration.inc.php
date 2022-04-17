<?php

/**
 * @file classes/migration/install/MetricsMigration.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class MetricsMigration
 * @brief Describe database table structures.
 */

namespace APP\migration\install;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema as Schema;

class MetricsMigration extends \PKP\migration\Migration
{
    /**
     * Run the migrations.
     * This migration file is used during upgrades. If this schema changes, the upgrade scripts should be reviewed manually before a merging.
     */
    public function up(): void
    {
        Schema::create('metrics_context', function (Blueprint $table) {
            $table->string('load_id', 255);
            $table->bigInteger('context_id');
            $table->date('date');
            $table->integer('metric');
            $table->foreign('context_id')->references('press_id')->on('presses');
            $table->index(['load_id'], 'metrics_context_load_id');
            $table->index(['context_id'], 'metrics_context_context_id');
        });
        Schema::create('metrics_series', function (Blueprint $table) {
            $table->string('load_id', 255);
            $table->bigInteger('context_id');
            $table->bigInteger('series_id');
            $table->date('date');
            $table->integer('metric');
            $table->foreign('context_id')->references('press_id')->on('presses');
            $table->foreign('series_id')->references('series_id')->on('series');
            $table->index(['load_id'], 'metrics_series_load_id');
            $table->index(['context_id', 'series_id'], 'metrics_series_context_id_series_id');
        });
        Schema::create('metrics_submission', function (Blueprint $table) {
            $table->string('load_id', 255);
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->bigInteger('chapter_id')->nullable();
            $table->bigInteger('representation_id')->nullable();
            $table->bigInteger('submission_file_id')->unsigned()->nullable();
            $table->bigInteger('file_type')->nullable();
            $table->bigInteger('assoc_type');
            $table->date('date');
            $table->integer('metric');
            $table->foreign('context_id')->references('press_id')->on('presses');
            $table->foreign('submission_id')->references('submission_id')->on('submissions');
            $table->foreign('chapter_id')->references('chapter_id')->on('submission_chapters');
            $table->foreign('representation_id')->references('publication_format_id')->on('publication_formats');
            $table->foreign('submission_file_id')->references('submission_file_id')->on('submission_files');
            $table->index(['load_id'], 'ms_load_id');
            $table->index(['context_id', 'submission_id', 'assoc_type', 'file_type'], 'ms_context_id_submission_id_assoc_type_file_type');
        });
        Schema::create('metrics_counter_submission_daily', function (Blueprint $table) {
            $table->string('load_id', 255);
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->date('date');
            $table->integer('metric_book_investigations');
            $table->integer('metric_book_investigations_unique');
            $table->integer('metric_book_requests');
            $table->integer('metric_book_requests_unique');
            $table->integer('metric_chapter_investigations');
            $table->integer('metric_chapter_investigations_unique');
            $table->integer('metric_chapter_requests');
            $table->integer('metric_chapter_requests_unique');
            $table->integer('metric_title_investigations_unique');
            $table->integer('metric_title_requests_unique');
            $table->foreign('context_id', 'msd_context_id_foreign')->references('press_id')->on('presses');
            $table->foreign('submission_id', 'msd_submission_id_foreign')->references('submission_id')->on('submissions');
            $table->index(['load_id'], 'msd_load_id');
            $table->index(['context_id', 'submission_id'], 'msd_context_id_submission_id');
            $table->unique(['load_id', 'context_id', 'submission_id', 'date'], 'msd_uc_load_id_context_id_submission_id_date');
        });
        Schema::create('metrics_counter_submission_monthly', function (Blueprint $table) {
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->string('month', 6);
            $table->integer('metric_book_investigations');
            $table->integer('metric_book_investigations_unique');
            $table->integer('metric_book_requests');
            $table->integer('metric_book_requests_unique');
            $table->integer('metric_chapter_investigations');
            $table->integer('metric_chapter_investigations_unique');
            $table->integer('metric_chapter_requests');
            $table->integer('metric_chapter_requests_unique');
            $table->integer('metric_title_investigations_unique');
            $table->integer('metric_title_requests_unique');
            $table->foreign('context_id', 'msm_context_id_foreign')->references('press_id')->on('presses');
            $table->foreign('submission_id', 'msm_submission_id_foreign')->references('submission_id')->on('submissions');
            $table->index(['context_id', 'submission_id'], 'msm_context_id_submission_id');
            $table->unique(['context_id', 'submission_id', 'month'], 'msm_uc_context_id_submission_id_month');
        });
        Schema::create('metrics_counter_submission_institution_daily', function (Blueprint $table) {
            $table->string('load_id', 255);
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->bigInteger('institution_id');
            $table->date('date');
            $table->integer('metric_book_investigations');
            $table->integer('metric_book_investigations_unique');
            $table->integer('metric_book_requests');
            $table->integer('metric_book_requests_unique');
            $table->integer('metric_chapter_investigations');
            $table->integer('metric_chapter_investigations_unique');
            $table->integer('metric_chapter_requests');
            $table->integer('metric_chapter_requests_unique');
            $table->integer('metric_title_investigations_unique');
            $table->integer('metric_title_requests_unique');
            $table->foreign('context_id', 'msid_context_id_foreign')->references('press_id')->on('presses');
            $table->foreign('submission_id', 'msid_submission_id_foreign')->references('submission_id')->on('submissions');
            $table->foreign('institution_id', 'msid_institution_id_foreign')->references('institution_id')->on('institutions');
            $table->index(['load_id'], 'msid_load_id');
            $table->index(['context_id', 'submission_id'], 'msid_context_id_submission_id');
            $table->unique(['load_id', 'context_id', 'submission_id', 'institution_id', 'date'], 'msid_uc_load_id_context_id_submission_id_institution_id_date');
        });
        Schema::create('metrics_counter_submission_institution_monthly', function (Blueprint $table) {
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->bigInteger('institution_id');
            $table->string('month', 6);
            $table->integer('metric_book_investigations');
            $table->integer('metric_book_investigations_unique');
            $table->integer('metric_book_requests');
            $table->integer('metric_book_requests_unique');
            $table->integer('metric_chapter_investigations');
            $table->integer('metric_chapter_investigations_unique');
            $table->integer('metric_chapter_requests');
            $table->integer('metric_chapter_requests_unique');
            $table->integer('metric_title_investigations_unique');
            $table->integer('metric_title_requests_unique');
            $table->foreign('context_id', 'msim_context_id_foreign')->references('press_id')->on('presses');
            $table->foreign('submission_id', 'msim_submission_id_foreign')->references('submission_id')->on('submissions');
            $table->foreign('institution_id', 'msim_institution_id_foreign')->references('institution_id')->on('institutions');
            $table->index(['context_id', 'submission_id'], 'msim_context_id_submission_id');
            $table->unique(['context_id', 'submission_id', 'institution_id', 'month'], 'msim_uc_context_id_submission_id_institution_id_month');
        });
        Schema::create('metrics_submission_geo_daily', function (Blueprint $table) {
            $table->string('load_id', 255);
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->date('date');
            $table->integer('metric');
            $table->integer('metric_unique');
            $table->foreign('context_id', 'msgd_context_id_foreign')->references('press_id')->on('presses');
            $table->foreign('submission_id', 'msgd_submission_id_foreign')->references('submission_id')->on('submissions');
            $table->index(['load_id'], 'msgd_load_id');
            $table->index(['context_id', 'submission_id'], 'msgd_context_id_submission_id');
            $table->unique(['load_id', 'context_id', 'submission_id', 'country', 'region', 'city', 'date'], 'msgd_uc_load_context_submission_c_r_c_date');
        });
        Schema::create('metrics_submission_geo_monthly', function (Blueprint $table) {
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('month', 6);
            $table->integer('metric');
            $table->integer('metric_unique');
            $table->foreign('context_id', 'msgm_context_id_foreign')->references('press_id')->on('presses');
            $table->foreign('submission_id', 'msgm_submission_id_foreign')->references('submission_id')->on('submissions');
            $table->index(['context_id', 'submission_id'], 'msgm_context_id_submission_id');
            $table->unique(['context_id', 'submission_id', 'country', 'region', 'city', 'month'], 'msgm_uc_context_submission_c_r_c_month');
        });
        // Usage stats total book and chapter item temporary records
        Schema::create('usage_stats_total_temporary_records', function (Blueprint $table) {
            $table->dateTime('date', $precision = 0);
            $table->string('ip', 255);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');
            $table->string('canonical_url', 255);
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id')->nullable();
            $table->bigInteger('chapter_id')->nullable();
            $table->bigInteger('representation_id')->nullable();
            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 255);
        });
        // Usage stats unique book and chapter item investigations temporary records
        Schema::create('usage_stats_unique_item_investigations_temporary_records', function (Blueprint $table) {
            $table->dateTime('date', $precision = 0);
            $table->string('ip', 255);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->bigInteger('chapter_id')->nullable();
            $table->bigInteger('representation_id')->nullable();
            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 255);
        });
        // Usage stats unique book and chapter item requests temporary records
        Schema::create('usage_stats_unique_item_requests_temporary_records', function (Blueprint $table) {
            $table->dateTime('date', $precision = 0);
            $table->string('ip', 255);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->bigInteger('chapter_id')->nullable();
            $table->bigInteger('representation_id')->nullable();
            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 255);
        });
        // Usage stats unique title investigations temporary records
        Schema::create('usage_stats_unique_title_investigations_temporary_records', function (Blueprint $table) {
            $table->dateTime('date', $precision = 0);
            $table->string('ip', 255);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->bigInteger('chapter_id')->nullable();
            $table->bigInteger('representation_id')->nullable();
            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 255);
        });
        // Usage stats unique title requests temporary records
        Schema::create('usage_stats_unique_title_requests_temporary_records', function (Blueprint $table) {
            $table->dateTime('date', $precision = 0);
            $table->string('ip', 255);
            $table->string('user_agent', 255);
            $table->bigInteger('line_number');
            $table->bigInteger('context_id');
            $table->bigInteger('submission_id');
            $table->bigInteger('chapter_id')->nullable();
            $table->bigInteger('representation_id')->nullable();
            $table->bigInteger('assoc_type');
            $table->bigInteger('assoc_id');
            $table->smallInteger('file_type')->nullable();
            $table->string('country', 2)->default('');
            $table->string('region', 3)->default('');
            $table->string('city', 255)->default('');
            $table->string('load_id', 255);
        });
        // Usage stats institution temporary records
        Schema::create('usage_stats_institution_temporary_records', function (Blueprint $table) {
            $table->string('load_id', 255);
            $table->bigInteger('line_number');
            $table->bigInteger('institution_id');
            $table->unique(['load_id', 'line_number', 'institution_id'], 'usitr_load_id_line_number_institution_id');
        });
    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::drop('metrics_context');
        Schema::drop('metrics_series');
        Schema::drop('metrics_submission');
        Schema::drop('metrics_counter_submission_daily');
        Schema::drop('metrics_counter_submission_monthly');
        Schema::drop('metrics_counter_submission_institution_daily');
        Schema::drop('metrics_counter_submission_institution_monthly');
        Schema::drop('metrics_submission_geo_daily');
        Schema::drop('metrics_submission_geo_monthly');
        Schema::drop('usage_stats_total_temporary_records');
        Schema::drop('usage_stats_unique_item_investigations_temporary_records');
        Schema::drop('usage_stats_unique_item_requests_temporary_records');
        Schema::drop('usage_stats_unique_title_investigations_temporary_records');
        Schema::drop('usage_stats_unique_title_requests_temporary_records');
        Schema::drop('usage_stats_institution_temporary_records');
    }
}
