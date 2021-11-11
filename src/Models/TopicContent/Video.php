<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use EscolaLms\TopicTypes\Events\VideoUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *      schema="TopicVideo",
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
 *      ),
 *      @OA\Property(
 *          property="poster",
 *          description="poster",
 *          type="string"
 *      )
 * )
 */
class Video extends AbstractTopicFileContent
{
    use HasFactory;

    public $table = 'topic_videos';

    public $fillable = [
        'value',
        'poster',
        'width',
        'height',
    ];

    protected $casts = [
        'id' => 'integer',
        'value' => 'string',
        'poster' => 'string',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public static function rules(): array
    {
        return [
            'value' => ['required', 'file', 'mimes:mp4,ogg,webm'],
            'poster' => ['file', 'image'],
        ];
    }

    protected $appends = ['url', 'poster_url'];

    protected static function newFactory()
    {
        return \EscolaLms\TopicTypes\Database\Factories\TopicContent\VideoFactory::new();
    }

    public function getStoragePathFinalSegment(): string
    {
        return 'video';
    }

    public function getPosterUrlAttribute(): ?string
    {
        if (isset($this->poster)) {
            return url(Storage::url($this->poster));
        }

        return null;
    }

    protected static function booted()
    {
        static::saved(function (Video $video) {
            if ($video->wasRecentlyCreated || $video->wasChanged('value')) {
                event(new VideoUpdated($video));
            }
        });
    }

    public function fixAssetPaths(): array
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $destination_value = sprintf('courses/%d/topic/%d/%s', $course->id, $topic->id, basename($this->value));
        $destination_poster = sprintf('courses/%d/topic/%d/%s', $course->id, $topic->id, basename($this->poster));
        $results = [];

        if (strpos($this->value, $destination_value) === false && Storage::exists($this->value)) {
            Storage::move($this->value, $destination_value);
            $results[] = [$this->value, $destination_value];
            $this->value = $destination_value;
        }

        if (strpos($this->poster, $destination_poster) === false && Storage::exists($this->poster)) {
            Storage::move($this->poster, $destination_poster);
            $results[] = [$this->poster, $destination_poster];
            $this->poster = $destination_poster;
        }

        if (count($results)) {
            $this->save();
        }

        return $results;
    }
}
