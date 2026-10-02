<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_as_seller_creates_both_profiles(): void
    {
        Mail::fake();

        $this->postJson('/api/register-api', [
            'fullname' => 'Seller One',
            'email' => 'seller@example.com',
            'phone_number' => '03001112233',
            'password' => 'secret12',
            'as_seller' => true,
        ])->assertCreated()
            ->assertJsonPath('data.user.is_seller', true)
            ->assertJsonPath('data.user.is_buyer', true);

        $user = User::where('email', 'seller@example.com')->first();
        $this->assertNotNull($user->buyerProfile);
        $this->assertNotNull($user->sellerProfile);
        $this->assertNotNull($user->sellerProfile->wallet);
    }

    public function test_buyer_can_become_seller(): void
    {
        $user = User::factory()->create();
        $user->ensureBuyerProfile();

        Sanctum::actingAs($user);

        $this->postJson('/api/become-seller')
            ->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.is_seller', true);

        $user->refresh();
        $this->assertTrue($user->is_seller);
        $this->assertTrue($user->is_buyer);
        $this->assertNotNull($user->sellerProfile);
        $this->assertNotNull($user->sellerProfile->wallet);
    }

    public function test_become_seller_is_idempotent(): void
    {
        $user = User::factory()->create();
        $user->becomeSeller();

        Sanctum::actingAs($user);

        $this->postJson('/api/become-seller')
            ->assertOk()
            ->assertJsonPath('message', 'You are already a seller.');

        $this->assertSame(1, $user->sellerProfile()->count());
    }

    public function test_admin_and_moderator_roles_exist_after_seed(): void
    {
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->assertTrue(Role::where('name', 'admin')->exists());
        $this->assertTrue(Role::where('name', 'moderator')->exists());
    }
}
