<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'course_id','is_published','title','instruction','file_path','url','due_at'
    ];

    protected $casts = [
        'is_published' => 'bool',
        'due_at'       => 'datetime',
    ];

    public function course()      { return $this->belongsTo(Course::class); }
    public function submissions() { return $this->hasMany(Submission::class); }

    public function scopeVisibleForLevel($q, int $studentLevel)
    {
        return $q->where('is_published', true)
                ->where(function ($w) use ($studentLevel) {
                    $w->whereNull('level')
                    ->orWhere('level', '<=', $studentLevel);
                });
    }
}

