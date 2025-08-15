<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_LESSON     = 'lesson';
    public const TYPE_WORKSHEET  = 'worksheet';
    public const TYPE_SELF_STUDY = 'self-study';

    protected $fillable = [
        'course_id','is_published','type','level','title','descriptions','file_path','url','uploaded_at',
    ];

    protected $casts = [
        'is_published' => 'bool',
        'uploaded_at'  => 'datetime',
    ];

    public function course() { return $this->belongsTo(Course::class); }

    /** Visibility: published + <= studentLevel (null = visible to all) */
    public function scopeVisibleForLevel(Builder $q, int $studentLevel): Builder
    {
        return $q->published()
                 ->where(function ($w) use ($studentLevel) {
                     $w->whereNull('level')->orWhere('level', '<=', $studentLevel);
                 });
    }

    /** Reusable small scopes for the dashboard/list pages */
    public function scopePublished(Builder $q): Builder    { return $q->where('is_published', true); }
    public function scopeType(Builder $q, string $t): Builder { return $q->where('type', $t); }
    public function scopeLevelIs(Builder $q, int $l): Builder { return $q->where('level', $l); }
    public function scopeForCourse(Builder $q, int $courseId): Builder { return $q->where('course_id', $courseId); }

    /** Quick helper for tile badges */
    public static function countsPerLevelType(int $courseId): array
    {
        return static::query()
            ->forCourse($courseId)->published()
            ->selectRaw('level, type, count(*) as total')
            ->groupBy('level','type')
            ->get()
            ->groupBy('level')   // [level => collect([{type,total},…])]
            ->map(fn($g) => $g->pluck('total','type'))
            ->toArray();
    }
}
