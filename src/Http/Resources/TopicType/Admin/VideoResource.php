<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Admin;

use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VideoResource extends JsonResource implements TopicTypeResourceContract
{
    public function toArray($request)
    {
        $urlValue = $this->hls ?: $this->value;
        return [
            'id' => $this->id,
            'value' => $urlValue,
            'url' => Storage::disk('local')->url($urlValue),
            'poster' => $this->poster,
            'poster_url' => $this->poster ? Storage::disk('local')->url($this->poster) : null,
            'width' => $this->width,
            'height' => $this->height,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
