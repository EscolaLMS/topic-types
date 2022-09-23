<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
use EscolaLms\TopicTypes\Database\Factories\TopicContent\H5PFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *      schema="TopicH5P",
 *      required={"value"},
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          @OA\Schema(
 *             type="integer",
 *         )
 *      ),
 *      @OA\Property(
 *          property="value",
 *          description="value",
 *          type="string"
 *      )
 * )
 */
class H5P extends AbstractTopicContent
{
    use HasFactory;

    public $table = 'topic_h5ps';

    public static function rules(): array
    {
        return [
            'value' => ['required', 'integer', 'exists:hh5p_contents,id'],
        ];
    }

    protected static function newFactory()
    {
        return H5PFactory::new();
    }

    public function fixAssetPaths(): array
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $destination = sprintf('course/%d/topic/%d/%s', $course->id, $topic->id, 'export.h5p');
        $contentRepository = App::make(H5PContentRepositoryContract::class);
        $filepath = $contentRepository->download($this->value);

        if (Storage::exists($destination)) {
            Storage::delete($destination);
        }

        $inputStream = fopen($filepath, 'r+');
        Storage::getDriver()->writeStream($destination, $inputStream);

        return [[$filepath, Storage::path($destination)]];
    }

    public function getMorphClass()
    {
        return self::class;
    }
}
