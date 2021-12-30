<?php

namespace EscolaLms\TopicTypes\Database\Factories\TopicContent\Components;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class FileHelper
{
    public static function uploadFile(int $topicId, string $name, string $ext = 'jpg'): array
    {
        $filename = "topic/$topicId/" . $name . '.' . $ext;
        $dest = Storage::disk('public')->path($filename);
        $destDir = dirname($dest);
        if (!is_dir($destDir) || (mkdir($destDir, 0777, true) && !is_dir($destDir))) {
            throw new DirectoryNotFoundException(sprintf('Directory "%s" was not created', $destDir));
        }
        copy(realpath(__DIR__.'/../../mocks/1.' . $ext), $dest);

        return [
            'value' => $filename,
        ];
    }
}
