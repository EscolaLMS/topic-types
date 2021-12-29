<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use EscolaLms\TopicTypes\Facades\Markdown;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicRichText",
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
class RichText extends AbstractTopicContent
{
    use HasFactory;

    public $table = 'topic_richtexts';

    public static function rules(): array
    {
        return [
            'value' => ['required', 'string'],
        ];
    }

    protected static function newFactory()
    {
        return \EscolaLms\TopicTypes\Database\Factories\TopicContent\RichTextFactory::new();
    }

    public function fixAssetPaths(): array
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $destinationPrefix = sprintf('courses/%d/topic/%d/', $course->id, $topic->id);

        $result = Markdown::convertImagesPathsForImageApi($this->value, $destinationPrefix);

        if ($result['value'] !== $this->value) {
            $this->value = $result['value'];
            $this->save();
        }

        return $result['results'];
    }

    public function getMorphClass()
    {
        return self::class;
    }
}
