<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name','nickname','faculty','language','email','password','role','line_id','phone_number',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    // 1:1 role profiles
    public function student()  { return $this->hasOne(Student::class); }
    public function lecturer() { return $this->hasOne(Lecturer::class); }
    public function admin()    { return $this->hasOne(Admin::class); }

    // Announcements posted by this user
    public function announcements() { return $this->hasMany(Announcement::class); }
}

