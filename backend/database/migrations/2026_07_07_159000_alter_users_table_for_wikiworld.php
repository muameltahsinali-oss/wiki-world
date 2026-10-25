<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Ramsey\Uuid\Uuid;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->unique();
            $table->string('display_name', 255)->nullable();
            $table->string('password_hash', 255)->nullable();
            $table->foreignId('preferred_language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->boolean('mfa_enabled')->default(false);
            $table->timestampTz('last_login_at')->nullable();
            $table->softDeletes();
        });

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            DB::table('users')->where('id', $user->id)->update([
                'uuid' => Uuid::uuid7()->toString(),
                'display_name' => $user->name,
                'password_hash' => $user->password,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password', 255)->nullable();
        });

        DB::table('users')->orderBy('id')->each(function (object $user): void {
            DB::table('users')->where('id', $user->id)->update([
                'password' => $user->password_hash,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropConstrainedForeignId('preferred_language_id');
            $table->dropColumn([
                'uuid',
                'display_name',
                'password_hash',
                'is_active',
                'mfa_enabled',
                'last_login_at',
            ]);
        });
    }
};
