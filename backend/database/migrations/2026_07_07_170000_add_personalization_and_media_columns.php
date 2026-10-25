<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_files', function (Blueprint $table): void {
            $table->string('status', 32)->default('processing')->after('mime_type');
            $table->string('alt_text', 512)->nullable()->after('original_filename');
        });

        Schema::create('reading_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->timestampTz('viewed_at');

            $table->index(['user_id', 'viewed_at']);
        });

        Schema::create('saved_searches', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 255);
            $table->json('query_payload');
            $table->timestampTz('created_at')->useCurrent();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_searches');
        Schema::dropIfExists('reading_history');

        Schema::table('media_files', function (Blueprint $table): void {
            $table->dropColumn(['status', 'alt_text']);
        });
    }
};
