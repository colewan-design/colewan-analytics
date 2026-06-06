<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('analytics_clicks', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_id', 40)->index();
            $table->string('session_id', 64)->index();
            $table->text('page_url');
            $table->string('element_tag', 50)->nullable();
            $table->string('element_id', 255)->nullable();
            $table->string('element_class', 500)->nullable();
            $table->string('element_text', 500)->nullable();
            $table->text('element_href')->nullable();
            $table->integer('x_pos')->nullable();
            $table->integer('y_pos')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_clicks');
    }
};
