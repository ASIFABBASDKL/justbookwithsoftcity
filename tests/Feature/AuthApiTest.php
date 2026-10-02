<?php

namespace Tests\Feature;

use App\Models\ServiceProvider;
use App\Models\ServiceUser;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_does_not_return_otp(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/register-api', [
            'fullname' => 'Test User',
            'email' => 'test@example.com',
            'phone_number' => '03001234567',
            'password' => 'secret12',
            'role' => 'user',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', true);

        $this->assertArrayNotHasKey('phone_otp', $response->json());
        $this->assertArrayNotHasKey('email_otp', $response->json());

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseHas('buyer_profiles', ['user_id' => User::first()->id]);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'is_buyer' => 1,
            'is_seller' => 0,
        ]);
    }

    public function test_login_requires_phone_verification(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'unverified@example.com',
            'password' => 'password',
        ]);

        $this->postJson('/api/login-api', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertForbidden()
            ->assertJsonPath('message', 'Please verify your phone number before login.');
    }

    public function test_login_returns_sanctum_token(): void
    {
        $user = User::factory()->create([
            'email' => 'ok@example.com',
            'password' => 'password',
        ]);
        ServiceUser::create(['user_id' => $user->id]);

        $this->postJson('/api/login-api', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['token', 'token_type', 'user']]);
    }

    public function test_protected_routes_require_authentication(): void
    {
        $this->postJson('/api/withdraw', [
            'service_provider_id' => 1,
            'wallet_id' => 1,
            'transaction_id' => 'tx-1',
            'account_number' => '123',
            'amount' => 10,
            'payment_method' => 'bank_transfer',
        ])->assertUnauthorized();

        $this->getJson('/api/me')->assertUnauthorized();
    }

    public function test_provider_cannot_withdraw_from_another_wallet(): void
    {
        $owner = User::factory()->provider()->create();
        $ownerProvider = ServiceProvider::create(['user_id' => $owner->id]);
        $wallet = Wallet::create([
            'service_provider_id' => $ownerProvider->id,
            'total_amount' => 100,
            'total_available_amount' => 100,
            'total_withdrawal_amount' => 0,
        ]);

        $attacker = User::factory()->provider()->create();
        ServiceProvider::create(['user_id' => $attacker->id]);

        Sanctum::actingAs($attacker);

        $this->postJson('/api/withdraw', [
            'service_provider_id' => $ownerProvider->id,
            'wallet_id' => $wallet->id,
            'transaction_id' => 'tx-steal',
            'account_number' => '999',
            'amount' => 10,
            'payment_method' => 'bank_transfer',
        ])->assertForbidden();
    }

    public function test_logout_revokes_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/logout-api')
            ->assertOk()
            ->assertJsonPath('status', true);
    }
}
