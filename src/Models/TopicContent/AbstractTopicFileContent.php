<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use EscolaLms\Courses\Models\Contracts\TopicFileContentContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

abstract class AbstractTopicFileContent extends AbstractTopicContent implements TopicFileContentContract
{
    protected $appends = ['url'];

    public function getFileKeyNames(): array
    {
        return Collection::make($this->rules())
            ->filter(function ($fieldRules) {
                if (is_array($fieldRules)) {

                    return in_array('file', $fieldRules) || in_array('image', $fieldRules);
                }

                return strpos('file', $fieldRules) !== false || strpos('image', $fieldRules) !== false;
            })
            ->keys()
            ->toArray();
    }

    public function generateStoragePath(?string $basePath = null): string
    {
        if (empty($basePath)) {
            if ($this->topic) {
                $basePath = $this->topic->storage_directory;
            } else {
                $basePath = 'topic-content/' . $this->getKey() . '/';
            }
        }

        return $basePath . $this->getStoragePathFinalSegment();
    }

    public function getUrlAttribute(): string
    {
        return url(Storage::url($this->value));
    }

    public function storeUploadsFromRequest(FormRequest $request, ?string $path = null): self
    {
        foreach ($this->getFileKeyNames() as $fileKey) {
            if ($request->hasFile($fileKey)) {
                $this->storeUpload($request->file($fileKey), $fileKey, $path);
            }
        }
        $this->processUploadedFiles();

        return $this;
    }

    protected function processUploadedFiles(): void
    {
        // do something in child classes
    }

    protected function storeUpload(UploadedFile $file, string $key = 'value', ?string $path = null): string
    {
        $this->{$key} = $file->storePublicly($this->generateStoragePath($path));

        return $this->{$key};
    }
}
