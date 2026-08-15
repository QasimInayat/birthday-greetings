<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsConfig extends Model
{
    use HasFactory;

    protected $table = 'sms_config';

    protected $fillable = [
        'api_url',
        'api_key',
        'sender_id'
    ];
}
