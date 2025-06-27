<?php

namespace App\Helper;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

readonly class FileHelper
{
    public function saveFile(UploadedFile $file, string $uploadDir, string $filename): File
    {
        if (!is_dir($uploadDir)) {
            $filesystem = new Filesystem();
            $filesystem->mkdir($uploadDir);
        }

        return $file->move($uploadDir, $filename);
    }

    public function deleteFile(string $filePath): void
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}