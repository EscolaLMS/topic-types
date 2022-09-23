<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *      schema="TopicPDF",
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
class PDF extends AbstractTopicFileContent
{
    use HasFactory;

    public $table = 'topic_pdfs';

    public static function rules(): array
    {
        return [
            'value' => ['required', 'mimes:pdf'],
        ];
    }

    protected static function newFactory()
    {
        return \EscolaLms\TopicTypes\Database\Factories\TopicContent\PdfFactory::new();
    }

    public function getStoragePathFinalSegment(): string
    {
        return 'pdf';
    }

    public function getMorphClass()
    {
        return self::class;
    }
}
