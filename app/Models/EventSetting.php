<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_type',
        'send_sms',
        'send_email',
        'milestones_only',
        'status',
    ];

    protected $casts = [
        'send_sms'        => 'boolean',
        'send_email'      => 'boolean',
        'milestones_only' => 'boolean',
        'status'          => 'boolean',
    ];

    /** Anniversary years that count as milestones. */
    public const MILESTONES = [1, 3, 5, 10, 15, 20, 25, 30];

    public static function forType(string $type): self
    {
        return static::firstOrCreate(
            ['event_type' => $type],
            ['send_sms' => true, 'send_email' => true, 'status' => false]
        );
    }

    public function isEnabled(): bool
    {
        return $this->status && ($this->send_sms || $this->send_email);
    }

    public function label(): string
    {
        return config('templates.types')[$this->event_type] ?? ucfirst($this->event_type);
    }
}
