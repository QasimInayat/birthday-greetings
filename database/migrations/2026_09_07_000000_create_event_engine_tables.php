<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Anniversaries need a date to match against.
        Schema::table('employees', function (Blueprint $table) {
            $table->date('date_of_joining')->nullable()->after('birthday');
        });

        // Without this, a birthday and an anniversary on the same day would
        // block each other in the duplicate guard.
        Schema::table('logs', function (Blueprint $table) {
            $table->string('event_type')->nullable()->after('type');
        });

        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->unique();
            $table->boolean('send_sms')->default(true);
            $table->boolean('send_email')->default(true);
            $table->boolean('milestones_only')->default(false); // anniversaries
            $table->boolean('status')->default(false);          // off until configured
            $table->timestamps();
        });

        // Birthday keeps its current behaviour; everything else starts disabled.
        $now = now();
        $rows = [];

        foreach (array_keys(config('templates.types', [])) as $type) {
            $rows[] = [
                'event_type'      => $type,
                'send_sms'        => true,
                'send_email'      => true,
                'milestones_only' => false,
                'status'          => $type === 'birthday',
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if ($rows) {
            DB::table('event_settings')->insert($rows);
        }

        // Existing history predates per-event logging; treat it as birthday.
        DB::table('logs')->whereNull('event_type')->update(['event_type' => 'birthday']);
    }

    public function down(): void
    {
        Schema::dropIfExists('event_settings');

        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('event_type');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('date_of_joining');
        });
    }
};
