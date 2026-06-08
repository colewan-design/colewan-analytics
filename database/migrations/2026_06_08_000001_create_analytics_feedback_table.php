<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_id', 40)->index();
            $table->string('session_id', 64)->nullable()->index();
            $table->text('page_url');
            $table->tinyInteger('rating'); // 1=unhappy, 2=neutral, 3=happy
            $table->text('comment')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_feedback');
    }
};
