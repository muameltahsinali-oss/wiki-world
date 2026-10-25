<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Users\Application\Contracts\UserRepositoryInterface;
use Illuminate\Database\Seeder;

final class DevUserRolesSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            return;
        }

        $repository = app(UserRepositoryInterface::class);

        $user = User::query()->where('email', 'test@example.com')->first();

        if ($user === null) {
            return;
        }

        foreach (['registered', 'editor', 'senior_editor', 'admin'] as $role) {
            try {
                $repository->assignRole($user->uuid, $role);
            } catch (\Throwable) {
                // Role may already be assigned.
            }
        }

        foreach (['quran', 'ahlulbayt', 'general', 'books_sources'] as $domain) {
            \Illuminate\Support\Facades\DB::table('editor_domain_scopes')->insertOrIgnore([
                'user_id' => $user->id,
                'domain_code' => $domain,
            ]);
        }
    }
}
