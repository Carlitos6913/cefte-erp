<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'first_surname',
        'second_surname',
        'identity_card',
        'phone',
        'address',
        'profession',
        'email',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->first_surname} ".($this->second_surname ?? ''));
    }
}
