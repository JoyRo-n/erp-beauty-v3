<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $table = 'employees';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'name' => \App\Casts\EncryptedString::class,
        'job_position' => \App\Casts\EncryptedString::class,
        'department' => \App\Casts\EncryptedString::class,
        'skills' => \App\Casts\EncryptedArray::class,
        'birthdate' => \App\Casts\EncryptedString::class,
        'phone' => \App\Casts\EncryptedString::class,
        'address' => \App\Casts\EncryptedString::class,
        'termination_reason' => \App\Casts\EncryptedString::class,
        'offboarding_checklist' => \App\Casts\EncryptedArray::class,
        'terminated_at' => 'datetime',
    ];

    public function treatments()
    {
        return $this->hasMany(BeautyTreatment::class, 'employee_id', 'id');
    }

    public function hasSkill(string $skill): bool
    {
        $skills = is_array($this->skills) ? $this->skills : explode(';', (string) $this->skills);
        $trimmedSkills = array_map('trim', $skills);
        return in_array($skill, $trimmedSkills);
    }
}
