<?php

namespace App\Http\Resources;

use App\Http\Resources\Appreciate as AppreciateResource;
use App\Http\Resources\Comment as CommentResource;
use App\Http\Resources\User as UserResource;
use App\Models\Act as ActModel;
use Dedoc\Scramble\Attributes\SchemaVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ActModel */
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
            'user' => UserResource::make($this->whenLoaded('user')),
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'appreciates_count' => (int) ($this->appreciates_count ?? 0),
            'comments_count' => (int) ($this->comments_count ?? 0),
            'appreciates' => AppreciateResource::collection($this->whenLoaded('appreciates')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
