<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The API exposes `profile_photo_path`. Databases carried over from the previous app already have the column.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'profile_photo_path')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path', 2048)->nullable();
        });
    }
};
