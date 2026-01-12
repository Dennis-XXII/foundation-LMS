<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['assignment_id','student_id','file_path','submitted_at'];
    protected $casts    = ['submitted_at' => 'datetime'];

    public function assignment() { return $this->belongsTo(Assignment::class); }
    public function student()    { return $this->belongsTo(Student::class); }
    public function assessment() { return $this->hasOne(Assessment::class); }
}

