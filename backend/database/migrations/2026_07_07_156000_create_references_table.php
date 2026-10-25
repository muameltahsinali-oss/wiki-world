<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_entity_id')->constrained('entities');
            $table->string('referencer_type', 32);
            $table->unsignedBigInteger('referencer_id');
            $table->foreignId('entity_id')->constrained('entities');
            $table->string('page_reference', 128)->nullable();
            $table->string('volume', 32)->nullable();
            $table->text('quote_excerpt')->nullable();
            $table->integer('sort_order')->nullable();
            $table->timestampTz('created_at');

            $table->index('source_entity_id');
            $table->index('entity_id');
            $table->index(['referencer_type', 'referencer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
