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
        Schema::create('entity_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64);
            $table->string('class', 32);
            $table->string('profile_table', 128)->nullable();
            $table->string('primary_domain', 64)->nullable();
            $table->boolean('is_mvp')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique('code');
            $table->index('class');
            $table->index('is_active');
        });

        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            DB::statement(
                "ALTER TABLE entity_types ADD CONSTRAINT entity_types_class_check
                CHECK (class IN ('canonical', 'composed', 'bibliographic', 'taxonomic', 'platform'))"
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('entity_types');
    }
};
