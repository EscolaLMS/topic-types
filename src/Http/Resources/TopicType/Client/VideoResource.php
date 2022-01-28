<?php

namespace EscolaLms\TopicTypes\Http\Resources\TopicType\Client;

use EscolaLms\Auth\Traits\ResourceExtandable;
use EscolaLms\TopicTypes\Http\Resources\TopicType\Contacts\TopicTypeResourceContract;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VideoResource extends JsonResource implements TopicTypeResourceContract
{
    use ResourceExtandable;

    public function toArray($request)
    {
        $fields = [
            'id' => $this->id,
            'value' => $this->value,
            'url' => $this->value ? Storage::disk('local')->url($this->value) : null,
            'poster' => $this->poster,
            'poster_url' => $this->poster ? Storage::disk('local')->url($this->poster) : null,
            'width' => $this->width,
            'height' => $this->height,
        ];

        return self::apply($fields, $this);
    }

    public static function apply(array $fields, JsonResource $thisObj): array {
        foreach (self::$extensions as $extension) {
            $fields = array_merge($fields, $extension($thisObj));
        }
        return $fields;
    }
}
