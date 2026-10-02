<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CommissionRule;
use App\Models\PlatformSetting;
use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'commission_percent' => '20',
            'auto_complete_days' => '3',
            'connect_price' => '1',
            'min_withdrawal' => '20',
            'monthly_free_connects' => '10',
            'pending_clearance_days' => '7',
            'max_revisions' => '3',
        ];
        foreach ($settings as $key => $value) {
            PlatformSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        CommissionRule::firstOrCreate(
            ['category_id' => null, 'seller_level' => null],
            ['percent' => 20, 'min_fee' => 0, 'is_active' => true]
        );

        $parents = ['Graphics & Design', 'Programming & Tech', 'Writing & Translation', 'Digital Marketing'];
        foreach ($parents as $i => $name) {
            $parent = Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $i, 'is_active' => true]
            );
            $child = $name === 'Programming & Tech' ? 'Web Development' : 'Logo Design';
            Category::firstOrCreate(
                ['slug' => Str::slug($child)],
                ['name' => $child, 'parent_id' => $parent->id, 'is_active' => true]
            );
        }

        foreach (['PHP', 'Laravel', 'React', 'Logo Design', 'Copywriting'] as $skill) {
            Skill::firstOrCreate(['slug' => Str::slug($skill)], ['name' => $skill]);
        }
    }
}
