<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type', 64);
            $table->foreignId('entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('session_hash', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestampTz('occurred_at');

            $table->index(['event_type', 'occurred_at']);
            $table->index(['entity_id', 'occurred_at']);
        });

        Schema::create('entity_popularity_daily', function (Blueprint $table) {
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('unique_sessions')->default(0);
            $table->unsignedInteger('bookmark_adds')->default(0);

            $table->primary(['entity_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_popularity_daily');
        Schema::dropIfExists('analytics_events');
    }
};
