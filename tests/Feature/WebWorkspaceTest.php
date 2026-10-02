<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WebWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_loads(): void
    {
        $this->get('/')->assertOk()->assertSee('JustBook');
    }

    public function test_buyer_dashboard_requires_auth(): void
    {
        $this->get('/app/buyer')->assertRedirect('/login');
    }

    public function test_buyer_can_open_dashboard(): void
    {
        $user = User::factory()->create();
        $user->ensureBuyerProfile();

        $this->actingAs($user)->get('/app/buyer')->assertOk()->assertSee('Buyer dashboard');
    }

    public function test_seller_onboard_then_dashboard(): void
    {
        $user = User::factory()->create();
        $user->ensureBuyerProfile();

        $this->actingAs($user)->get('/app/seller')->assertRedirect(route('web.seller.onboard'));
        $this->actingAs($user)->post('/app/seller/onboard')->assertRedirect(route('web.seller'));
        $this->actingAs($user->fresh())->get('/app/seller')->assertOk()->assertSee('Seller dashboard');
    }

    public function test_admin_dashboard_forbidden_for_regular_user(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/app/admin')->assertForbidden();
    }

    public function test_admin_can_open_dashboard(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->assignRole(Role::findByName('admin'));

        $this->actingAs($user)->get('/app/admin')->assertOk()->assertSee('Admin dashboard');
    }
}
