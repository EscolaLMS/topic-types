<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *      schema="TopicAudio",
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
class Audio extends AbstractTopicFileContent
{
    use HasFactory;

    public $table = 'topic_audios';

    protected $fillable = [
        'value',
        'length',
    ];

    protected $casts = [
        'id' => 'integer',
        'value' => 'string',
        'length' => 'integer',
    ];

    public static function rules(): array
    {
        return [
            'value' => ['required', 'file', 'mimes:mp3,ogg'],
            'length' => ['sometimes', 'integer'],
        ];
    }

    protected static function newFactory()
    {
        return \EscolaLms\TopicTypes\Database\Factories\TopicContent\AudioFactory::new();
    }

    protected function processUploadedFiles(): void
    {
        $this->length = 0;
    }

    public function getStoragePathFinalSegment(): string
    {
        return 'audio';
    }

    public function fixAssetPaths(): array
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $basename = basename($this->value);
        $destination = sprintf('courses/%d/topic/%d/%s', $course->id, $topic->id, $basename);
        $results = [];

        if (strpos($this->value, $destination) === false && Storage::exists($this->value)) {
            Storage::move($this->value, $destination);
            $results[] = [$this->value, $destination];
            $this->value = $destination;
            $this->save();
        }

        return $results;
    }
}
