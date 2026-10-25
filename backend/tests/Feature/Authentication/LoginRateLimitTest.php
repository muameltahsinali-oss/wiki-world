<?php

declare(strict_types=1);

namespace Tests\Feature\Authentication;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class LoginRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
    }

    public function test_login_locks_after_five_failed_attempts(): void
    {
        User::factory()->create([
            'email' => 'locked@wikiworld.test',
            'password_hash' => Hash::make('SecurePass123!'),
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'locked@wikiworld.test',
                'password' => 'WrongPassword123!',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'locked@wikiworld.test',
            'password' => 'WrongPassword123!',
        ])
            ->assertStatus(423)
            ->assertJsonPath('error.code', 'ACCOUNT_LOCKED');
    }

    public function test_successful_login_clears_rate_limit(): void
    {
        User::factory()->create([
            'email' => 'clear@wikiworld.test',
            'password_hash' => Hash::make('SecurePass123!'),
        ]);

        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/auth/login', [
                'email' => 'clear@wikiworld.test',
                'password' => 'WrongPassword123!',
            ])->assertUnauthorized();
        }

        $this->postJson('/api/v1/auth/login', [
            'email' => 'clear@wikiworld.test',
            'password' => 'SecurePass123!',
        ])->assertOk();
    }
}
