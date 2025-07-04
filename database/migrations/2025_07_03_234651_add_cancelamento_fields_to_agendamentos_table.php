<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->text('observacoes_cancelamento')->nullable()->after('observacoes');
            $table->enum('cancelado_por', ['usuario', 'admin', 'sistema'])->nullable()->after('observacoes_cancelamento');
            $table->timestamp('cancelado_em')->nullable()->after('cancelado_por');
            
            $table->index(['status', 'data_agendamento']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down()
    {
        Schema::table('agendamentos', function (Blueprint $table) {
            $table->dropColumn(['observacoes_cancelamento', 'cancelado_por', 'cancelado_em']);
            $table->dropIndex(['status', 'data_agendamento']);
            $table->dropIndex(['user_id', 'status']);
        });
    }
};
