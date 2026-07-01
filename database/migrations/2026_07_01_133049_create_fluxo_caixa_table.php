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
        Schema::create('fluxo_caixa', function (Blueprint $table) {
            $table->id();
            $table->uuid('uid')->unique();
            $table->string('descricao');
            $table->double('valor');
            $table->enum('tipo_movimentacao', ['entrada', 'saida']);
            $table->string('forma_pagamento');
            $table->timestamp('data_vencimento')->nullable();
            $table->timestamp('data_pagamento')->nullable();
            $table->boolean('pago')->default(false);
            $table->text('observacao')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fluxo_caixa');
    }
};
