<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\TopicTypes\Events\EscolaLmsTopicTypeChangedTemplateEvent;
use EscolaLms\TopicTypes\Models\Contracts\TopicContentContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;

abstract class AbstractTopicContent extends Model implements TopicContentContract
{
    protected $fillable = [
        'value',
    ];

    protected $casts = [
        'id' => 'integer',
        'value' => 'string',
    ];

    protected static function booted()
    {
        $user = Auth::user();
        static::saved(function (AbstractTopicContent $topicContent) use ($user) {
            if (
                ($topicContent->wasRecentlyCreated || $topicContent->wasChanged('value')) &&
                $user
            ){
                event(new EscolaLmsTopicTypeChangedTemplateEvent($user, $topicContent));
            }
        });
    }

    public static function rules(): array
    {
        return [
            'value' => ['required'],
        ];
    }

    public function topic(): MorphOne
    {
        return $this->morphOne(Topic::class, 'topicable');
    }

    public function fixAssetPaths(): array
    {
        return [];
    }
}
