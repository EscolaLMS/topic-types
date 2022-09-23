<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use EscolaLms\TopicTypes\Database\Factories\TopicContent\VideoFactory;
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
            'value' => ['required', 'mimes:mp4,ogg,webm'],
            'poster' => ['file', 'image'],
        ];
    }

    protected $appends = ['url', 'poster_url'];

    protected static function newFactory()
    {
        return VideoFactory::new();
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

    public function fixAssetPaths(): array
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $destinationValue = sprintf(
            'course/%d/topic/%d/video/%s',
            $course->id,
            $topic->id,
            basename($this->value)
        );
        $destinationPoster = sprintf(
            'course/%d/topic/%d/video/%s',
            $course->id,
            $topic->id,
            basename($this->poster)
        );
        $results = [];
        if (strpos($this->value, $destinationValue) === false && Storage::exists($this->value)) {
            if (!Storage::exists($destinationValue)) {
                Storage::move($this->value, $destinationValue);
            }
            $results[] = [$this->value, $destinationValue];
            $this->value = $destinationValue;
        }
        if (strpos($this->poster, $destinationPoster) === false && Storage::exists($this->poster)) {
            if (!Storage::exists($destinationPoster)) {
                Storage::move($this->poster, $destinationPoster);
            }
            $results[] = [$this->poster, $destinationPoster];
            $this->poster = $destinationPoster;
        }
        if (count($results)) {
            $this->save();
        }

        return $results;
    }

    public function getMorphClass()
    {
        return self::class;
    }
}
