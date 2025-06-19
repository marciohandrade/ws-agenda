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
            // Tornar campos opcionais para cadastro público
            $table->date('data_nascimento')->nullable()->change();
            $table->string('cep', 9)->nullable()->change();
            $table->string('endereco', 80)->nullable()->change();
            $table->string('numero', 10)->nullable()->change();
            
            // Já são nullable, mas garantindo
            $table->string('genero', 50)->nullable()->change();
            $table->string('cpf', 14)->nullable()->change();
            $table->string('complemento', 30)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            // Voltar campos para obrigatórios
            $table->date('data_nascimento')->nullable(false)->change();
            $table->string('cep', 9)->nullable(false)->change();
            $table->string('endereco', 80)->nullable(false)->change();
            $table->string('numero', 10)->nullable(false)->change();
        });
    }
};