<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Appreciations are soft-deleted along with their act or comment, so restoring the act or comment brings them back.
     */
    public function up(): void
    {
        Schema::table('appreciates', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
