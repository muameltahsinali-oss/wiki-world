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
        Schema::create('search_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('entity_id')->unique()->constrained('entities')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages');
            $table->string('entity_type_code', 64);
            $table->text('title');
            $table->text('body_text')->nullable();
            $table->json('module_flags');
            $table->json('facets');
            $table->decimal('popularity_score', 12, 4)->default(0);
            $table->text('tsv')->nullable();
            $table->string('meilisearch_id', 128)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampTz('indexed_at')->nullable();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index('language_id');
            $table->index('entity_type_code');
            $table->index('is_active');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE search_documents ALTER COLUMN tsv TYPE tsvector USING NULL');
            DB::statement('CREATE INDEX search_documents_tsv_gin ON search_documents USING GIN (tsv)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('search_documents');
    }
};
