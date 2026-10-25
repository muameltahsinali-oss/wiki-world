<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relationship_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('label_en', 128);
            $table->string('label_ar', 128);
            $table->string('inverse_code', 64)->nullable();
            $table->boolean('is_symmetric')->default(false);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('relationship_type_allowed_pairs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relationship_type_id')->constrained('relationship_types')->cascadeOnDelete();
            $table->foreignId('source_entity_type_id')->constrained('entity_types');
            $table->foreignId('target_entity_type_id')->constrained('entity_types');

            $table->unique(
                ['relationship_type_id', 'source_entity_type_id', 'target_entity_type_id'],
                'relationship_type_allowed_pairs_unique',
            );
        });

        Schema::create('entity_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('relationship_type_id')->constrained('relationship_types');
            $table->foreignId('source_entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('target_entity_id')->constrained('entities')->cascadeOnDelete();
            $table->json('metadata')->default('{}');
            $table->integer('sort_order')->nullable();
            $table->timestampTz('valid_from')->nullable();
            $table->timestampTz('valid_to')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('source_entity_id');
            $table->index('target_entity_id');
            $table->index('relationship_type_id');
            $table->unique(
                ['relationship_type_id', 'source_entity_id', 'target_entity_id'],
                'entity_relationships_unique_edge',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_relationships');
        Schema::dropIfExists('relationship_type_allowed_pairs');
        Schema::dropIfExists('relationship_types');
    }
};
