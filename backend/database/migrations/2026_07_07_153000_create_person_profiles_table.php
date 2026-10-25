<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_profiles', function (Blueprint $table) {
            $table->foreignId('entity_id')->primary()->constrained('entities')->cascadeOnDelete();
            $table->string('kunya', 255)->nullable();
            $table->json('titles')->nullable();
            $table->string('birth_date_hijri', 32)->nullable();
            $table->date('birth_date_gregorian')->nullable();
            $table->string('death_date_hijri', 32)->nullable();
            $table->date('death_date_gregorian')->nullable();
            $table->boolean('is_masum')->default(false);
            $table->smallInteger('ahlulbayt_generation')->nullable();
            $table->smallInteger('imam_order')->nullable();
            $table->string('gender', 16)->nullable();
            $table->json('attributes')->default('{}');
            $table->timestamps();

            $table->index('is_masum');
            $table->index('ahlulbayt_generation');
            $table->index('imam_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_profiles');
    }
};
