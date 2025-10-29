<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoalNotReachedConfigResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'state' => $this->state,
            'details' => GoalNotReachedConfigDetailResource::collection($this->whenLoaded('details')),
        ];
    }
}
