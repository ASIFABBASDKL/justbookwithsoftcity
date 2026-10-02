<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Gig;
use App\Models\User;
use Database\Seeders\MarketplaceSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MarketplaceFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_gig_order_delivery_complete_review(): void
    {
        $this->seed(RoleSeeder::class);
        $this->seed(MarketplaceSeeder::class);

        $sellerUser = User::factory()->create();
        $sellerUser->becomeSeller();
        $buyer = User::factory()->create();
        $buyer->ensureBuyerProfile();

        Sanctum::actingAs($sellerUser);
        $cat = Category::first();
        $create = $this->postJson('/api/gigs', [
            'title' => 'I will build a Laravel API',
            'description' => 'Full REST API',
            'category_id' => $cat->id,
            'packages' => [
                [
                    'tier' => 'basic',
                    'title' => 'Starter',
                    'price' => 50,
                    'delivery_days' => 3,
                    'revisions' => 1,
                    'features' => ['API'],
                ],
            ],
        ]);
        $create->assertCreated();
        $gigId = $create->json('data.id');
        Gig::find($gigId)->update(['status' => 'active']);

        $packageId = Gig::find($gigId)->packages()->first()->id;

        Sanctum::actingAs($buyer);
        $order = $this->postJson('/api/orders', [
            'gig_package_id' => $packageId,
        ]);
        $order->assertCreated();
        $orderId = $order->json('data.id');

        $this->postJson("/api/orders/{$orderId}/pay")->assertOk()
            ->assertJsonPath('data.status', 'active');

        Sanctum::actingAs($sellerUser);
        $this->postJson("/api/orders/{$orderId}/deliver", [
            'note' => 'Done',
        ])->assertCreated();

        Sanctum::actingAs($buyer);
        $this->postJson("/api/orders/{$orderId}/complete")->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->postJson('/api/order-reviews', [
            'order_id' => $orderId,
            'rating' => 5,
            'comment' => 'Great',
        ])->assertCreated();
    }

    public function test_job_proposal_hire_creates_order(): void
    {
        $this->seed(MarketplaceSeeder::class);
        $buyer = User::factory()->create();
        $buyer->ensureBuyerProfile();
        $seller = User::factory()->create();
        $seller->becomeSeller();

        Sanctum::actingAs($buyer);
        $job = $this->postJson('/api/jobs', [
            'title' => 'Need a logo',
            'description' => 'Simple logo',
            'budget_min' => 20,
            'budget_max' => 100,
        ])->assertCreated();
        $jobId = $job->json('data.id');

        Sanctum::actingAs($seller);
        $proposal = $this->postJson("/api/jobs/{$jobId}/proposals", [
            'cover_letter' => 'I can do this',
            'bid_amount' => 40,
            'delivery_days' => 4,
        ])->assertCreated();

        Sanctum::actingAs($buyer);
        $this->postJson('/api/proposals/'.$proposal->json('data.id').'/hire')
            ->assertCreated()
            ->assertJsonPath('data.source_type', 'proposal');
    }
}
