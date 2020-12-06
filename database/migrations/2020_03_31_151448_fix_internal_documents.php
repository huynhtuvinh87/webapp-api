<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Legacy internal documents are called 'internal' while new documents are called 'internal_documents'
 * This migration is to convert all 'internal' to 'internal_documents' to avoid bugs with legacy documents
 *
 * https://contractorcomplianceio.atlassian.net/browse/DEV-1289
 */
class FixInternalDocuments extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $query = "UPDATE requirements
            SET type = 'internal_document'
            WHERE type = 'internal'";
        $res = DB::statement($query);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Legacy documents had the created_at column = null
        $query = "UPDATE requirements
            SET type = 'internal'
            WHERE type = 'internal_document'
            AND created_at IS NULL";
        $res = DB::statement($query);
    }
}
