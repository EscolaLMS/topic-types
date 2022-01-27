<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Export;

use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;
use Peopleaps\Scorm\Model\ScormScoModel;

class ScormScoResource extends JsonResource implements TopicTypeResourceContract
{
    public function toArray($request)
    {
        $topic = $this->topic;
        $destination = sprintf('topic/%d/%s', $topic->id, 'export.zip');
        $scormSco = ScormScoModel::find($this->value);

        return [
            'id' => $this->id,
            'value' => $this->value,
            'uuid' => $scormSco ? $scormSco->uuid : null,
            'identifier' => $scormSco ? $scormSco->identifier : null,
            'scorm_file' => $destination,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
