<?php

declare(strict_types=1);

namespace Tests\Feature\Authentication;

use App\Models\User;
use Database\Seeders\LanguageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

final class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([LanguageSeeder::class, RolePermissionSeeder::class]);
        Notification::fake();
    }

    public function test_register_sends_verification_notification(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'email' => 'verify@wikiworld.test',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ])->assertCreated();

        Notification::assertSentTo(
            User::query()->where('email', 'verify@wikiworld.test')->first(),
            VerifyEmail::class,
        );
    }

    public function test_verify_email_marks_user_as_verified(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
        );

        $this->getJson($url)
            ->assertOk()
            ->assertJsonPath('data.message', 'verified');

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    public function test_unverified_user_cannot_access_me(): void
    {
        $user = User::factory()->unverified()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/me')
            ->assertForbidden()
            ->assertJsonPath('error.code', 'EMAIL_NOT_VERIFIED');
    }

    public function test_invalid_verification_hash_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => 'invalid-hash'],
        );

        $this->getJson($url)
            ->assertForbidden()
            ->assertJsonPath('error.code', 'INVALID_VERIFICATION_LINK');
    }
}
