<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['submission_id','lecturer_id','score','comment','graded_at'];
    protected $casts    = ['assessed_at' => 'datetime'];

    public function submission() { return $this->belongsTo(Submission::class); }
    public function lecturer()   { return $this->belongsTo(Lecturer::class); }
}

