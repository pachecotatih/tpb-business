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
        Schema::table('fluxo_caixa', function (Blueprint $table) {
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->onDelete('set null');
            $table->foreignId('agendamento_id')->nullable()->constrained('agendamentos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fluxo_caixa', function (Blueprint $table) {
            $table->dropForeign(['cliente_id']);
            $table->dropForeign(['agendamento_id']);
            $table->dropColumn(['cliente_id', 'agendamento_id']);
        });
    }
};
