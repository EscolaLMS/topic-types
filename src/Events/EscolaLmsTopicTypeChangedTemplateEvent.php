<?php

namespace EscolaLms\TopicTypes\Events;

use EscolaLms\TopicTypes\Models\TopicContent\AbstractTopicContent;
use EscolaLms\TopicTypes\Models\TopicContent\Video;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EscolaLmsTopicTypeChangedTemplateEvent
{
    use Dispatchable;
    use SerializesModels;

    private AbstractTopicContent $topicContent;
    private Authenticatable $user;

    public function __construct(Authenticatable $user, AbstractTopicContent $topicContent)
    {
        $this->topicContent = $topicContent;
        $this->user = $user;
    }

    public function getTopicContent(): AbstractTopicContent
    {
        return $this->topicContent;
    }

    public function getUser(): Authenticatable
    {
        return $this->user;
    }
}
