<?php

namespace App\Models;

use App\Models\Concerns\HasDefaultTemplate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    use HasFactory, HasDefaultTemplate;

    protected $fillable = [
        'template_name',
        'template_type',
        'message',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
