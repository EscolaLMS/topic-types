<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

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
        return [];
        // // https://stackoverflow.com/questions/44227270/regex-to-parse-image-link-in-markdown
    }
}
