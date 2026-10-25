<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->integer('version_number');
            $table->text('change_summary')->nullable();
            $table->json('snapshot');
            $table->boolean('is_published_snapshot')->default(false);
            $table->foreignId('created_by_user_id')->constrained('users');
            $table->timestampTz('created_at');

            $table->unique(['entity_id', 'version_number']);
            $table->index('entity_id');
            $table->index('created_at');
        });

        Schema::create('editorial_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('entity_version_id')->constrained('entity_versions')->cascadeOnDelete();
            $table->foreignId('submitted_by_user_id')->constrained('users');
            $table->string('status', 32);
            $table->json('checklist_completed');
            $table->timestampTz('submitted_at');
            $table->timestampTz('withdrawn_at')->nullable();
        });

        Schema::create('editorial_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('submission_id')->constrained('editorial_submissions')->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->constrained('users');
            $table->string('decision', 32);
            $table->text('decision_notes')->nullable();
            $table->timestampTz('reviewed_at');
        });

        Schema::create('editorial_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('submission_id')->nullable()->constrained('editorial_submissions')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('comment_text');
            $table->boolean('is_resolved')->default(false);
            $table->timestampTz('created_at');
        });

        Schema::create('publish_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('entity_version_id')->constrained('entity_versions');
            $table->string('action', 32);
            $table->foreignId('performed_by_user_id')->constrained('users');
            $table->timestampTz('performed_at');
            $table->json('metadata')->nullable();

            $table->index('entity_id');
            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publish_events');
        Schema::dropIfExists('editorial_comments');
        Schema::dropIfExists('editorial_reviews');
        Schema::dropIfExists('editorial_submissions');
        Schema::dropIfExists('entity_versions');
    }
};
