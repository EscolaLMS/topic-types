<?php

namespace EscolaLms\TopicTypes\Events;

use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EscolaLmsTopicTypeChangedTemplateEvent
{
    use Dispatchable;
    use SerializesModels;

    private Video $video;
    private Authenticatable $user;

    public function __construct(Authenticatable $user, Video $video)
    {
        $this->video = $video;
        $this->user = $user;
    }

    public function getVideo(): Video
    {
        return $this->video;
    }

    public function getUser(): Authenticatable
    {
        return $this->user;
    }
}
