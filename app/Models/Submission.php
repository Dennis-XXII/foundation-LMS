<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Submission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['special_project_id','student_id','file_path','submitted_at'];
    protected $casts    = ['submitted_at' => 'datetime'];

    public function specialProject() { return $this->belongsTo(SpecialProject::class); }
    public function student()    { return $this->belongsTo(Student::class); }
    public function assessment() { return $this->hasOne(Assessment::class); }
}

