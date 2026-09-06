<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'department',
        'designation',
        'birthday',
        'date_of_joining',
        'gender',
        'profile_image',
        'status'
    ];

    protected $casts = [
        'birthday'        => 'date',
        'date_of_joining' => 'date',
    ];

    /** Completed years of service, or null when the join date is unknown. */
    public function yearsOfService(): ?int
    {
        if (!$this->date_of_joining) {
            return null;
        }

        return $this->date_of_joining->diffInYears(now());
    }
}
