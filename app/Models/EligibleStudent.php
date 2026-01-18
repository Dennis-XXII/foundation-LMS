<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EligibleStudent extends Model
{
    protected $fillable = ['student_id'];

    /**
     * Check if this whitelisted ID has a registered student account.
     */
    public function registeredStudent()
    {
        // Links the whitelist ID to the actual student record
        return $this->hasOne(Student::class, 'student_id', 'student_id');
    }
}