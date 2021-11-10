<?php

namespace EscolaLms\TopicTypes\Services;

use EscolaLms\Courses\Models\Topic;
use EscolaLms\TopicTypes\Services\Contracts\TopicTypeServiceContract;

class TopicTypeService implements TopicTypeServiceContract
{
    public function fixAssetPaths(): array
    {
        $results = [];
        // I hate imperative programming, but I'm so lazy ....
        foreach (Topic::all() as $topic) {
            $topicable = $topic->topicable;
            if (isset($topicable)) {
                foreach ($topic->topicable->fixAssetPaths() as $fix) {
                    $results[] = $fix;
                }
            }
        }

        return $results;
    }

    public function fixTopicTypeColumnName(): int
    {
        $i = 0;

        $topics = Topic::where('topicable_type', 'like', 'EscolaLms\\\\Courses\\\\Models\\\\TopicContent%')->get();

        foreach ($topics as $topic) {
            $topic->topicable_type = str_replace('EscolaLms\Courses\Models\TopicContent', "EscolaLms\TopicTypes\Models\TopicContent", $topic->topicable_type);
            $topic->save();
            ++$i;
        }

        return $i;
    }
}
