<?php

namespace App\Http\Resources;

use App\Http\Resources\User as UserResource;
use App\Models\Appreciate as AppreciateModel;
use Dedoc\Scramble\Attributes\SchemaVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AppreciateModel */
#[SchemaVariant(name: 'AppreciateResource', whenLoaded: [], default: true)]
#[SchemaVariant(name: 'AppreciateResourceWithUser', whenLoaded: ['user'])]
class Appreciate extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appreciable_id' => $this->appreciable_id,
            'appreciable_type' => $this->appreciable_type,
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_at' => $this->created_at,
        ];
    }
}
