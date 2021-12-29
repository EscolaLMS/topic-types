<?php

namespace EscolaLms\TopicTypes\Models\TopicContent;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\TopicTypes\Events\EscolaLmsTopicTypeChangedTemplateEvent;
use EscolaLms\TopicTypes\Models\Contracts\TopicContentContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

abstract class AbstractTopicContent extends \EscolaLms\Courses\Models\TopicContent\AbstractTopicContent implements TopicContentContract
{
    protected static function booted()
    {
        $user = Auth::user();
        static::saved(function (AbstractTopicContent $topicContent) use ($user) {
            if (
                ($topicContent->wasRecentlyCreated || $topicContent->wasChanged('value')) &&
                $user
            )
            {
                event(new EscolaLmsTopicTypeChangedTemplateEvent($user, $topicContent));
            }
        });
    }

    public function fixAssetPaths(): array
    {
        $topic = $this->topic;
        $course = $topic->lesson->course;
        $basename = basename($this->value);
        $destination = sprintf('courses/%d/topic/%d/%s', $course->id, $topic->id, $basename);
        $results = [];

        if (strpos($this->value, $destination) === false && Storage::exists($this->value)) {
            if (!Storage::exists($destination)) {
                Storage::move($this->value, $destination);
            }
            $results[] = [$this->value, $destination];
            $this->value = $destination;
            $this->save();
        }

        return $results;
    }
}
