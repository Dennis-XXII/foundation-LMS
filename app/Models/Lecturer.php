<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lecturer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id'];

    public function user()       { return $this->belongsTo(User::class); }
    public function courses()    { return $this->belongsToMany(Course::class, 'course_lecturers'); }
    public function assessments(){ return $this->hasMany(Assessment::class); }
}

