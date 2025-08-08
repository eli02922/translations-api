<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TranslationResources extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this->key,
            'locale' => $this->locale,
            'value' => $this->value,
            'tags' => $this->tags ? explode(',', $this->tags) : []
        ];
    }
}
