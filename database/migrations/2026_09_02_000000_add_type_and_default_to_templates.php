<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            // Match the types email templates already use.
            $table->string('template_type')->default('birthday')->after('template_name');
            $table->boolean('is_default')->default(false)->after('message');
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('content');
        });

        // Carry the existing choice over: whatever SMS Settings pointed at becomes
        // the default birthday template, so behaviour does not change on deploy.
        $selected = DB::table('sms_settings')->value('sms_template_id');

        if ($selected && DB::table('sms_templates')->where('id', $selected)->exists()) {
            DB::table('sms_templates')->where('id', $selected)
                ->update(['template_type' => 'birthday', 'is_default' => true]);
        } elseif ($first = DB::table('sms_templates')->orderBy('id')->first()) {
            DB::table('sms_templates')->where('id', $first->id)
                ->update(['template_type' => 'birthday', 'is_default' => true]);
        }

        // One default per email template type, keeping the current birthday pick.
        foreach (DB::table('email_templates')->select('template_type')->distinct()->pluck('template_type') as $type) {
            $pick = DB::table('email_templates')->where('template_type', $type)->orderBy('id')->first();

            if ($pick) {
                DB::table('email_templates')->where('id', $pick->id)->update(['is_default' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('sms_templates', function (Blueprint $table) {
            $table->dropColumn(['template_type', 'is_default']);
        });

        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
