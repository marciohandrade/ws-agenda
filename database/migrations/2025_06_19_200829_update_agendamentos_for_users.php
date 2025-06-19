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
        Schema::table('agendamentos', function (Blueprint $table) {
            // Adiciona referência direta ao user para otimização
            $table->foreignId('user_id')->nullable()
                  ->constrained('users')->onDelete('set null')
                  ->after('cliente_id');
            
            // Campo para identificar origem do agendamento
            $table->enum('origem', ['publico', 'admin', 'colaborador'])
                  ->default('publico')->after('user_id');
            
            // Índices
            $table->index('user_id');
            $table->index('origem');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['origem']);
            $table->dropColumn(['user_id', 'origem']);
        });
    }
};