<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Export;

use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;
use Peopleaps\Scorm\Model\ScormScoModel;

class ScormScoResource extends JsonResource implements TopicTypeResourceContract
{
    public function toArray($request)
    {
        $topic = $this->resource->topic;
        $destination = sprintf('topic/%d/%s', $topic->resource->id, 'export.zip');
        $scormSco = ScormScoModel::find($this->resource->value);

        return [
            'id' => $this->resource->id,
            'value' => $this->resource->value,
            'uuid' => $scormSco ? $scormSco->resource->uuid : null,
            'identifier' => $scormSco ? $scormSco->resource->identifier : null,
            'scorm_file' => $destination,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
