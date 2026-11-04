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
        'skills' => 'array',
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
