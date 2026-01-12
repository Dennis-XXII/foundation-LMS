<?php
namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class FilePathBuilder
{
    public static function materialPath(int $courseId, string $type, UploadedFile $file): string
    {
        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        return "course_{$courseId}/{$type}/".now()->format('Y/m').'/'.Str::random(8)."_{$slug}.".$file->getClientOriginalExtension();
    }

    public static function assignmentPath(int $courseId, int $assignmentId, UploadedFile $file): string
    {
        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        return "course_{$courseId}/assignment_{$assignmentId}/{$slug}.".$file->getClientOriginalExtension();
    }

    public static function submissionPath(int $assignmentId, int $studentId, UploadedFile $file): string
    {
        $slug = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        return "assignment_{$assignmentId}/student_{$studentId}/".now()->timestamp."_{$slug}.".$file->getClientOriginalExtension();
    }
}
