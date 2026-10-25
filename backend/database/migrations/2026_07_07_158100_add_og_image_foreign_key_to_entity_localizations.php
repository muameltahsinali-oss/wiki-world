<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entity_localizations', function (Blueprint $table) {
            $table->foreign('og_image_media_id')
                ->references('id')
                ->on('media_files')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('entity_localizations', function (Blueprint $table) {
            $table->dropForeign(['og_image_media_id']);
        });
    }
};
