<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('analytics_pageviews', function (Blueprint $table) {
            $table->string('view_id', 36)->nullable()->unique()->after('id');
            $table->unsignedInteger('duration')->nullable()->after('language')->comment('seconds on page');
        });
    }

    public function down(): void
    {
        Schema::table('analytics_pageviews', function (Blueprint $table) {
            $table->dropColumn(['view_id', 'duration']);
        });
    }
};
