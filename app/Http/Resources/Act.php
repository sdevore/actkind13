<?php

namespace App\Http\Resources;

use Dedoc\Scramble\Attributes\SchemaVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

#[SchemaVariant(name: 'ActResource', whenLoaded: [], default: true)]
#[SchemaVariant(name: 'ActResourceWithAuth', whenLoaded: ['user'])]
class Act extends JsonResource
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
            'user' => $this->whenLoaded('user'),
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'appreciates_count' => $this->appreciates_count ?? 0,
            'comments_count' => $this->comments_count ?? 0,
            'appreciates'=> $this->whenLoaded('appreciates'),
            'comments'=> $this->whenLoaded('comments'),
        ];
    }
}
