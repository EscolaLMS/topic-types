<?php

namespace EscolaLms\TopicTypes\Events;

use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EscolaLmsTopicTypeChangedTemplateEvent
{
    use Dispatchable;
    use SerializesModels;

    private Video $video;

    public function __construct(Video $video)
    {
        $this->video = $video;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }
}
