<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'title' => $this->title,
            'status' => $this->status,
            'price' => $this->price,
            'extras_total' => $this->extras_total,
            'platform_fee' => $this->platform_fee,
            'seller_earning' => $this->seller_earning,
            'currency' => $this->currency,
            'due_at' => $this->due_at,
            'is_late' => $this->is_late,
            'revisions_allowed' => $this->revisions_allowed,
            'revisions_used' => $this->revisions_used,
            'buyer_id' => $this->buyer_id,
            'seller_id' => $this->seller_id,
            'items' => $this->whenLoaded('items'),
            'activities' => $this->whenLoaded('activities'),
            'deliveries' => $this->whenLoaded('deliveries'),
            'milestones' => $this->whenLoaded('milestones'),
        ];
    }
}
