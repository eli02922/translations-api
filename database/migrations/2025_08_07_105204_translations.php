<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table): void {
            $table->id();
            $table->string('key');
            $table->string('locale');
            $table->string('tag')->nullable();
            $table->text('value');
            $table->unique(['key', 'locale', 'tag']);
            $table->timestamps();
            $table->index('tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
