<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entity_aliases', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('entity_id')->constrained('entities')->cascadeOnDelete();
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->string('text', 512);
            $table->string('alias_type', 32);
            $table->timestampTz('created_at')->useCurrent();

            $table->index('entity_id');
            $table->index('text');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_aliases');
    }
};
