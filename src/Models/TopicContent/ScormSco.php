<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicScormSco",
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
class ScormSco extends AbstractTopicContent
{
    use HasFactory;

    public $table = 'topic_scorm_scos';

    public static function rules(): array
    {
        return [
            'value' => ['required', 'integer', 'exists:scorm_sco,id'],
        ];
    }
}
