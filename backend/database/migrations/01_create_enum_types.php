<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Create status ENUM type
        DB::statement("
            CREATE TYPE status AS ENUM (
                'under_review',
                'reviewed_by_secretary',
                'verified_by_admin',
                'approved_by_head',
                'rejected',
                'needs_revision_by_secretary',
                'needs_revision_by_admin',
                'needs_revision_by_head'
            )
        ");

        // Create approval_action ENUM type
        DB::statement("
            CREATE TYPE approval_action AS ENUM (
                'approved',
                'rejected',
                'request_revision'
            )
        ");
    }

    public function down()
    {
        DB::statement("DROP TYPE IF EXISTS approval_action");
        DB::statement("DROP TYPE IF EXISTS status");
    }
};
