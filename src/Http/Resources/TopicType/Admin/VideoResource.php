<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Admin;

use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VideoResource extends JsonResource implements TopicTypeResourceContract
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'url' => $this->value ? Storage::disk('local')->url($this->value) : null,
            'poster' => $this->poster,
            'poster_url' => $this->poster ? Storage::disk('local')->url($this->poster) : null,
            'width' => $this->width,
            'height' => $this->height,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
