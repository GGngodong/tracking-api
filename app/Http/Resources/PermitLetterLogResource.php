<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermitLetterLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'status_tahapan' => $this->status_tahapan,
            'description'    => $this->description,
            'updated_by'     => $this->whenLoaded('editor', fn() => $this->editor->username),
            'created_at'     => optional($this->created_at)->toDateTimeString(),
            'updated_at'     => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
