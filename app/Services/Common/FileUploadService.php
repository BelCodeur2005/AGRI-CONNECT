<?php

// app/Services/Common/FileUploadService.php
namespace App\Services\Common;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

class FileUploadService
{
    /**
     * Upload image avec compression
     */
    public function uploadImage(UploadedFile $file, string $directory, int $maxWidth = 1200): string
    {
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = "{$directory}/{$filename}";

        // Compresser image
        $image = Image::make($file);
        
        if ($image->width() > $maxWidth) {
            $image->resize($maxWidth, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Sauvegarder
        Storage::disk('public')->put($path, (string) $image->encode());

        return $path;
    }

    /**
     * Upload fichier générique
     */
    public function uploadFile(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    /**
     * Supprimer fichier
     */
    public function delete(string $path): void
    {
        Storage::disk('public')->delete($path);
    }

    /**
     * Upload multiple images
     */
    public function uploadMultipleImages(array $files, string $directory, int $maxWidth = 1200): array
    {
        $paths = [];
        
        foreach ($files as $file) {
            $paths[] = $this->uploadImage($file, $directory, $maxWidth);
        }

        return $paths;
    }
}