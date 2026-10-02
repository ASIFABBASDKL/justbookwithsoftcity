<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'status' => $this->status,
            'avg_rating' => $this->avg_rating,
            'orders_count' => $this->orders_count,
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category'),
            'packages' => $this->whenLoaded('packages'),
            'extras' => $this->whenLoaded('extras'),
            'gallery' => $this->whenLoaded('gallery'),
            'faqs' => $this->whenLoaded('faqs'),
            'requirements' => $this->whenLoaded('requirements'),
            'seller' => $this->whenLoaded('seller'),
            'created_at' => $this->created_at,
        ];
    }
}
