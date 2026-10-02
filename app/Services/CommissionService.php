<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\PlatformSetting;

class CommissionService
{
    public function percent(?int $categoryId = null, ?string $sellerLevel = null): float
    {
        $rule = CommissionRule::query()
            ->where('is_active', true)
            ->where(function ($q) use ($categoryId, $sellerLevel) {
                $q->whereNull('category_id')->orWhere('category_id', $categoryId);
            })
            ->when($sellerLevel, fn ($q) => $q->where(function ($q) use ($sellerLevel) {
                $q->whereNull('seller_level')->orWhere('seller_level', $sellerLevel);
            }))
            ->orderByDesc('category_id')
            ->first();

        if ($rule) {
            return (float) $rule->percent;
        }

        return PlatformSetting::number('commission_percent', 20);
    }

    public function breakdown(float $subtotal, ?int $categoryId = null, ?string $level = null): array
    {
        $percent = $this->percent($categoryId, $level);
        $fee = round($subtotal * ($percent / 100), 2);
        $min = (float) (CommissionRule::query()->where('is_active', true)->value('min_fee') ?? 0);
        $fee = max($fee, $min);

        return [
            'subtotal' => $subtotal,
            'percent' => $percent,
            'platform_fee' => $fee,
            'seller_earning' => round($subtotal - $fee, 2),
        ];
    }
}
