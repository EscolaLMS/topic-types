<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use EscolaLms\HeadlessH5P\Repositories\Contracts\H5PContentRepositoryContract;
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
        return \EscolaLms\TopicTypes\Database\Factories\TopicContent\H5PFactory::new();
    }

    public function fixAssetPaths(): array
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $destination = sprintf('courses/%d/topic/%d/%s', $course->id, $topic->id, 'export.h5p');

        $contentRepository = App::make(H5PContentRepositoryContract::class);
        $filepath = $contentRepository->download($this->value);

        $disk = Storage::disk('local'); // this is always 'local' for h5p

        if ($disk->exists($destination)) {
            $disk->delete($destination);
        }

        $destination_path = $disk->path($destination);

        @\mkdir(dirname($destination_path), 0777, true);

        copy($filepath, $destination_path);

        // $disk->copy($filepath, $destination);

        return [[$filepath, $destination_path]];
    }
}
