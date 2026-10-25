<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('entity_version_id')->constrained('entity_versions')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('block_type', 32);
            $table->json('content');
            $table->unsignedSmallInteger('heading_level')->nullable();
            $table->timestamps();

            $table->index(['entity_id', 'language_id', 'entity_version_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
