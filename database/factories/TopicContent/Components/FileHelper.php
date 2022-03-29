<?php

namespace EscolaLms\TopicTypes\Database\Factories\TopicContent\Components;

use EscolaLms\Courses\Models\Topic;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class FileHelper
{
    public static function uploadFile(int $topicId, string $name, string $ext = 'jpg'): array
    {
        $topic = Topic::find($topicId);
        $filename = $topic->storage_directory . $name . '.' . $ext;
        $dest = Storage::path($filename);
        $destDir = dirname($dest);
        if (!is_dir($destDir) && (mkdir($destDir, 0777, true) && !is_dir($destDir))) {
            throw new DirectoryNotFoundException(sprintf('Directory "%s" was not created', $destDir));
        }
        $mockPath = realpath(__DIR__.'/../../../mocks');
        copy($mockPath . '/1.' . $ext, $dest);

        return [
            'value' => $filename,
        ];
    }
}
