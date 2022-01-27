<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Client;

use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;
use Peopleaps\Scorm\Model\ScormScoModel;

class ScormScoResource extends JsonResource implements TopicTypeResourceContract
{
    public function toArray($request)
    {
        $scormSco = ScormScoModel::find($this->value);

        return [
            'id' => $this->id,
            'value' => $this->value,
            'uuid' => $scormSco ? $scormSco->uuid : null,
        ];
    }
}
