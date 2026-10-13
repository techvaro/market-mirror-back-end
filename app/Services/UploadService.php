<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Stores user-uploaded media on the public disk and produces web-accessible
 * URLs. Files are stored under hashed, time-stamped directories so uploaded
 * filenames never reach the filesystem and cannot be used for traversal.
 */
class UploadService
{
    public function store(UploadedFile $file, string $directory = 'uploads'): string
    {
        $extension = $file->extension();

        $path = $file->storeAs(
            $directory,
            Str::uuid().'.'.$extension,
            'public'
        );

        return Storage::disk('public')->url($path);
    }

    public function deleteByUrl(?string $url): void
    {
        if ($url === null || $url === '') {
            return;
        }

        $disk = Storage::disk('public');

        $relativePath = Str::of($url)
            ->after(Str::finish($disk->url('/'), '/'));

        if ($disk->exists($relativePath)) {
            $disk->delete($relativePath);
        }
    }

    public function appendToJsonCollection(array $photos, string $url, int $limit = 10): array
    {
        $collection = array_values($photos);

        if (count($collection) >= $limit) {
            return $collection;
        }

        $collection[] = $url;

        return $collection;
    }
}
