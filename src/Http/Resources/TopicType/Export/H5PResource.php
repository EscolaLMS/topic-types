<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Export;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;

class H5PResource extends JsonResource implements TopicTypeResourceContract
{
    public function toArray($request)
    {
        $topic = $this->topic;
        $destination = sprintf('topic/%d/%s', $topic->id, 'export.h5p');

        return [
            'value' => $this->value,
            // 'content' => H5PContent::find($this->value),
            'h5p_file' => $destination,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
