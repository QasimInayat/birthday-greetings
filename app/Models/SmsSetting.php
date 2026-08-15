<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use HasFactory;

    protected $table = 'sms_settings';

    protected $fillable = [
        'daily_limit',
        'sms_template_id',
        'sender_id',
        'status'
    ];

    public function template()
    {
        return $this->belongsTo(SmsTemplate::class, 'sms_template_id');
    }
}
