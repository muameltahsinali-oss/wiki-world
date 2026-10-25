<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('media_type', 32);
            $table->string('storage_disk', 32);
            $table->text('storage_path');
            $table->string('original_filename', 512)->nullable();
            $table->string('mime_type', 128);
            $table->unsignedBigInteger('file_size_bytes');
            $table->string('checksum_sha256', 64)->nullable();
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampTz('created_at');
        });

        Schema::create('media_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_file_id')->constrained('media_files')->cascadeOnDelete();
            $table->string('variant_type', 32);
            $table->text('storage_path');
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
        });

        Schema::create('entity_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('media_file_id')->constrained('media_files')->cascadeOnDelete();
            $table->string('role', 32);
            $table->integer('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_media');
        Schema::dropIfExists('media_variants');
        Schema::dropIfExists('media_files');
    }
};
