<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cron_settings', function (Blueprint $table) {
            $table->id();
            $table->string('frequency')->default('daily'); // daily, weekly
            $table->time('run_time')->default('02:00:00');
            $table->boolean('status')->default(true); // enable/disable cron
            $table->timestamp('last_run')->nullable();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_settings');
    }
};
