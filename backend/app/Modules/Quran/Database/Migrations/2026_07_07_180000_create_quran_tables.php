<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quran_corpus', function (Blueprint $table): void {
            $table->smallIncrements('id');
            $table->foreignId('entity_id')->unique()->constrained('entities')->cascadeOnDelete();
            $table->string('script_edition', 128);
            $table->smallInteger('total_surahs');
            $table->integer('total_verses');
            $table->timestampTz('seeded_at');
        });

        Schema::create('quran_surahs', function (Blueprint $table): void {
            $table->smallInteger('surah_number')->primary();
            $table->foreignId('entity_id')->unique()->constrained('entities')->cascadeOnDelete();
            $table->smallInteger('verse_count');
            $table->string('revelation_type', 16);
            $table->boolean('bismillah_prefix')->default(true);
        });

        Schema::create('quran_verses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('entity_id')->unique()->constrained('entities')->cascadeOnDelete();
            $table->smallInteger('surah_number');
            $table->smallInteger('ayah_number');
            $table->text('text_uthmani');
            $table->text('text_normalized');
            $table->smallInteger('juz_number')->nullable();
            $table->smallInteger('hizb_number')->nullable();

            $table->foreign('surah_number')->references('surah_number')->on('quran_surahs');
            $table->unique(['surah_number', 'ayah_number']);
            $table->index('surah_number');
        });

        Schema::create('translation_profiles', function (Blueprint $table): void {
            $table->foreignId('entity_id')->primary()->constrained('entities')->cascadeOnDelete();
            $table->foreignId('verse_entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->foreignId('source_entity_id')->constrained('entities')->cascadeOnDelete();
            $table->text('translated_text');

            $table->unique(['verse_entity_id', 'language_id', 'source_entity_id'], 'translation_profiles_unique');
        });

        Schema::create('tafsir_profiles', function (Blueprint $table): void {
            $table->foreignId('entity_id')->primary()->constrained('entities')->cascadeOnDelete();
            $table->foreignId('verse_entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('source_entity_id')->constrained('entities')->cascadeOnDelete();
            $table->text('excerpt_text');
            $table->foreignId('language_id')->constrained('languages');
        });

        Schema::create('quran_topics', function (Blueprint $table): void {
            $table->id();
            $table->string('slug', 255)->unique();
            $table->string('title', 512);
            $table->text('description')->nullable();
            $table->foreignId('entity_id')->nullable()->constrained('entities')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('quran_topic_verses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->constrained('quran_topics')->cascadeOnDelete();
            $table->foreignId('verse_id')->constrained('quran_verses')->cascadeOnDelete();

            $table->unique(['topic_id', 'verse_id']);
        });

        Schema::create('quran_cross_references', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('source_verse_id')->constrained('quran_verses')->cascadeOnDelete();
            $table->smallInteger('target_surah_number');
            $table->smallInteger('target_ayah_number');
            $table->string('relationship', 32)->default('thematic');

            $table->index(['source_verse_id']);
            $table->index(['target_surah_number', 'target_ayah_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quran_cross_references');
        Schema::dropIfExists('quran_topic_verses');
        Schema::dropIfExists('quran_topics');
        Schema::dropIfExists('tafsir_profiles');
        Schema::dropIfExists('translation_profiles');
        Schema::dropIfExists('quran_verses');
        Schema::dropIfExists('quran_surahs');
        Schema::dropIfExists('quran_corpus');
    }
};
