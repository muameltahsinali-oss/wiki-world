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
        Schema::create('entities', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('entity_type_id')->constrained('entity_types');
            $table->string('workflow_state', 32)->default('draft');
            $table->json('module_flags')->default('{}');
            $table->boolean('is_indexable')->default(true);
            $table->timestampTz('published_at')->nullable();
            $table->unsignedBigInteger('published_version_id')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('entity_type_id');
            $table->index('workflow_state');
            $table->index('published_at');
            $table->index(['workflow_state', 'entity_type_id']);
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                "ALTER TABLE entities ADD CONSTRAINT entities_workflow_state_check
                CHECK (workflow_state IN (
                    'draft', 'submitted', 'in_review', 'approved',
                    'published', 'unpublished', 'archived'
                ))"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entities');
    }
};
