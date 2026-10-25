<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_profiles', function (Blueprint $table) {
            $table->foreignId('entity_id')->primary()->constrained('entities')->cascadeOnDelete();
            $table->string('start_date_hijri', 32);
            $table->string('end_date_hijri', 32)->nullable();
            $table->date('start_date_gregorian')->nullable();
            $table->date('end_date_gregorian')->nullable();
            $table->string('date_precision', 16);
            $table->string('era_code', 64)->nullable();
            $table->text('significance')->nullable();
            $table->json('attributes')->default('{}');
            $table->timestamps();
        });

        Schema::create('place_profiles', function (Blueprint $table) {
            $table->foreignId('entity_id')->primary()->constrained('entities')->cascadeOnDelete();
            $table->string('place_type', 64);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('modern_country_code', 8)->nullable();
            $table->json('historical_names')->nullable();
            $table->json('attributes')->default('{}');
            $table->timestamps();

            $table->index('place_type');
        });

        Schema::create('concept_profiles', function (Blueprint $table) {
            $table->foreignId('entity_id')->primary()->constrained('entities')->cascadeOnDelete();
            $table->string('arabic_term', 255);
            $table->string('concept_category', 64);
            $table->string('pronunciation', 255)->nullable();
            $table->json('attributes')->default('{}');
            $table->timestamps();
        });

        Schema::create('source_profiles', function (Blueprint $table) {
            $table->foreignId('entity_id')->primary()->constrained('entities')->cascadeOnDelete();
            $table->string('source_type', 32);
            $table->smallInteger('reliability_tier');
            $table->integer('publication_year')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->text('url')->nullable();
            $table->integer('page_count')->nullable();
            $table->integer('usage_count')->default(0);
            $table->string('copyright_status', 64)->nullable();
            $table->json('attributes')->default('{}');
            $table->timestamps();

            $table->index('source_type');
            $table->index('reliability_tier');
            $table->index('usage_count');
        });

        Schema::create('book_profiles', function (Blueprint $table) {
            $table->foreignId('entity_id')->primary()->constrained('entities')->cascadeOnDelete();
            $table->integer('publication_year')->nullable();
            $table->string('isbn', 20)->nullable();
            $table->foreignId('primary_language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->string('copyright_status', 64);
            $table->string('license_type', 64)->nullable();
            $table->json('attributes')->default('{}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_profiles');
        Schema::dropIfExists('source_profiles');
        Schema::dropIfExists('concept_profiles');
        Schema::dropIfExists('place_profiles');
        Schema::dropIfExists('event_profiles');
    }
};
