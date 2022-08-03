<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Admin;

use EscolaLms\HeadlessH5P\Models\H5PContent;
use EscolaLms\HeadlessH5P\Services\Contracts\HeadlessH5PServiceContract;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;

class H5PResource extends JsonResource implements TopicTypeResourceContract
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'content' => app(HeadlessH5PServiceContract::class)->getContentSettings($this->value),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
