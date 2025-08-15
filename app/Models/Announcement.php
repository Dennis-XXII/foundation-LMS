<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id','title','description','file_path','is_global','posted_at'];

    protected $casts = [
        'is_global' => 'bool',
        'posted_at' => 'datetime',
    ];

    public function user()    { return $this->belongsTo(User::class); }
    public function courses() { return $this->belongsToMany(Course::class, 'announcement_courses'); }
}

