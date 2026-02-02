<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the 'links_semanas' table.
 *
 * Tabela 'links_semanas' com os campos: id, data, link.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cria a tabela 'links_semanas' com os campos id (auto-increment),
     * data (DATE) e link (STRING).
     */
    public function up(): void
    {
        Schema::create('links_semanas', function (Blueprint $table): void {
            $table->id();
            $table->date('data');
            $table->string('link');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Remove a tabela 'links_semanas' caso exista.
     */
    public function down(): void
    {
        Schema::dropIfExists('links_semanas');
    }
};