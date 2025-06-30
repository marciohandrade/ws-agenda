<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Alterar o ENUM para incluir super_admin
        DB::statement("ALTER TABLE users MODIFY tipo_usuario ENUM('super_admin', 'admin', 'colaborador', 'usuario') DEFAULT 'usuario'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Voltar ao ENUM original (remover super_admin)
        DB::statement("ALTER TABLE users MODIFY tipo_usuario ENUM('admin', 'colaborador', 'usuario') DEFAULT 'usuario'");
    }
};