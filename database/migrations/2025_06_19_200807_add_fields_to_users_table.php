
bash
php artisan migrate:status
php artisan tinker
>>> Schema::hasColumn('users', 'telefone')
>>> Schema::hasColumn('clientes', 'user_id')
🎯 EXECUTOU AS MIGRATIONS?
Se sim, vamos para o próximo passo:

Criação do formulário de cadastro público
Validações de telefone único
Sistema de envio de SMS
Se deu algum erro, me avise que ajusto!

Confirma que as migrations rodaram sem problema?








<?php
// ==================================================
// MIGRATION 1: 2025_06_19_001_add_fields_to_users_table.php
// ==================================================

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
        Schema::table('users', function (Blueprint $table) {
            // Adiciona telefone obrigatório e único
            $table->string('telefone')->unique()->after('email');
            
            // Adiciona tipo de usuário
            $table->enum('tipo_usuario', ['admin', 'colaborador', 'usuario'])
                  ->default('usuario')->after('telefone');
            
            // Campo para controle de verificação SMS
            $table->timestamp('telefone_verified_at')->nullable()->after('email_verified_at');
            
            // Token temporário para verificação SMS
            $table->string('sms_verification_token', 6)->nullable()->after('telefone_verified_at');
            
            // Timestamp do token SMS para expiração
            $table->timestamp('sms_token_expires_at')->nullable()->after('sms_verification_token');
            
            // Índices para performance
            $table->index('tipo_usuario');
            $table->index('telefone_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['tipo_usuario']);
            $table->dropIndex(['telefone_verified_at']);
            $table->dropColumn([
                'telefone',
                'tipo_usuario',
                'telefone_verified_at',
                'sms_verification_token',
                'sms_token_expires_at'
            ]);
        });
    }
};