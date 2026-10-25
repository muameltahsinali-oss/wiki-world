<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_metadata', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->string('meta_title', 512)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_override', 1024)->nullable();
            $table->string('robots', 64)->nullable();
            $table->uuid('og_image_uuid')->nullable();
            $table->json('structured_data')->nullable();
            $table->timestampTz('updated_at')->useCurrent();

            $table->unique(['entity_id', 'language_id']);
        });

        Schema::create('url_redirects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('language_id')->constrained('languages');
            $table->string('from_path', 1024);
            $table->string('to_path', 1024)->nullable();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->unsignedSmallInteger('redirect_type')->default(301);
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['language_id', 'from_path']);
        });

        Schema::create('sitemap_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->string('loc_path', 1024);
            $table->timestampTz('lastmod');
            $table->string('changefreq', 16)->nullable();
            $table->decimal('priority', 2, 1)->nullable();
            $table->boolean('is_active')->default(true);

            $table->unique(['entity_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sitemap_entries');
        Schema::dropIfExists('url_redirects');
        Schema::dropIfExists('seo_metadata');
    }
};
