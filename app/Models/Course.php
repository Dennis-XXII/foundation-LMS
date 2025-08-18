<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code','name','level','description'];

    public function lecturers()   { return $this->belongsToMany(Lecturer::class, 'course_lecturers'); }
    public function enrollments() { return $this->hasMany(Enrollment::class); }
    public function materials()   { return $this->hasMany(Material::class); }
    public function assignments() { return $this->hasMany(Assignment::class); }
    public function announcements(){ return $this->belongsToMany(Announcement::class, 'announcement_courses'); }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'enrollments')
                    ->withPivot('level','status')
                    ->withTimestamps();
    }
}

