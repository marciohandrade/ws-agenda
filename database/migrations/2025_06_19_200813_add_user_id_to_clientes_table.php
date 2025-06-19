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
        Schema::table('clientes', function (Blueprint $table) {
            // Relacionamento com users (nullable para clientes existentes)
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->onDelete('set null')
                  ->after('id');
            
            // Índice para performance
            $table->index('user_id');
            
            // Tornar email e telefone únicos (importante para integração)
            $table->string('email')->unique()->change();
            $table->string('telefone')->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropColumn('user_id');
            
            // Remove constraints únicos
            $table->dropUnique(['email']);
            $table->dropUnique(['telefone']);
        });
    }
};