<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Export;

use EscolaLms\Cmi5\Models\Cmi5Au;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;

class Cmi5AuResource extends JsonResource implements TopicTypeResourceContract
{
    public function toArray($request)
    {
        $topic = $this->topic;
        $destination = sprintf('topic/%d/%s', $topic->id, 'export.zip');
        $cmi5Au = Cmi5Au::find($this->value);

        return [
            'id' => $this->id,
            'value' => $this->value,
            'iri' => $cmi5Au ? $cmi5Au->iri : null,
            'url' => $cmi5Au ? $cmi5Au->url : null,
            'cmi5_file' => $destination,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
