<?php

namespace App\Http\Resources;

use App\Models\Comment as CommentModel;
use Dedoc\Scramble\Attributes\SchemaVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CommentModel */
#[SchemaVariant(name: 'CommentResource', whenLoaded: [], default: true)]
#[SchemaVariant(name: 'CommentResourceWithUser', whenLoaded: ['user'])]
class Comment extends JsonResource
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
            'act_id' => $this->act_id,
            'user' => $this->whenLoaded('user'),
            'body' => $this->body,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
