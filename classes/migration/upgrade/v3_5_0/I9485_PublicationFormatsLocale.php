<?php

/**
 * @file classes/migration/upgrade/v3_5_0/I9485_PublicationFormatsLocale.php
 *
 * Copyright (c) 2024 Simon Fraser University
 * Copyright (c) 2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class I9485_PublicationFormatsLocale
 *
 * @brief Add column locale to the DB table publication_formats.
 */

namespace APP\migration\upgrade\v3_5_0;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PKP\migration\Migration;

class I9485_PublicationFormatsLocale extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('publication_formats', function (Blueprint $table) {
            $table->string('locale', 28)->nullable();
        });
        DB::table('publication_formats as pf')
            ->join('publications as p', 'p.publication_id', '=', 'pf.publication_id')
            ->join('submissions as s', 's.submission_id', '=', 'p.submission_id')
            ->update(['pf.locale' => DB::raw('s.locale')]);

    }

    /**
     * Reverse the migration.
     */
    public function down(): void
    {
        Schema::table('publication_formats', function (Blueprint $table) {
            if (Schema::hasColumn($table->getTable(), 'locale')) {
                $table->dropColumn('maslocalethead');
            };
        });
    }
}
