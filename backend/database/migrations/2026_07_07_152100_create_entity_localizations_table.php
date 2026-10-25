<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_localizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->string('title', 512);
            $table->string('slug', 512);
            $table->text('summary')->nullable();
            $table->string('meta_title', 255)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->unsignedBigInteger('og_image_media_id')->nullable();
            $table->string('translation_status', 32)->default('draft');
            $table->string('workflow_state', 32)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->timestamps();

            $table->unique(['entity_id', 'language_id']);
            $table->index('language_id');
            $table->index('slug');
            $table->index('translation_status');
            $table->index(['language_id', 'slug']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                "ALTER TABLE entity_localizations ADD CONSTRAINT entity_localizations_translation_status_check
                CHECK (translation_status IN ('draft', 'published', 'fallback'))"
            );
            DB::statement(
                "ALTER TABLE entity_localizations ADD CONSTRAINT entity_localizations_workflow_state_check
                CHECK (workflow_state IN (
                    'draft', 'submitted', 'in_review', 'approved',
                    'published', 'unpublished', 'archived'
                ))"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_localizations');
    }
};
