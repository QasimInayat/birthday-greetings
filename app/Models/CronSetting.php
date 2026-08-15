<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CronSetting extends Model
{
    use HasFactory;

    protected $table = 'cron_settings';

    protected $fillable = [
        'frequency',
        'run_time',
        'status',
        'last_run'
    ];
}
