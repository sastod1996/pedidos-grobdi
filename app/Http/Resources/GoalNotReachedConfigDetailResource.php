<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GoalNotReachedConfigDetailResource extends JsonResource
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
            'initial_percentage' => $this->formatted_initial_percentage,
            'final_percentage' => $this->formatted_final_percentage,
            'commission' => $this->formatted_commission,
        ];
    }
}
