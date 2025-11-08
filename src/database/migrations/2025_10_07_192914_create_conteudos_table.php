<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conteudos', function (Blueprint $table) {
            $table->id();

            // Campos de Conteúdo e Rastreamento
            $table->string('papel'); // Ex: Analista Financeiro
            $table->string('ticker'); // Ativo analisado, Ex: 'PETR4'
            $table->text('conteudo'); // Conteúdo longo (artigo) da IA

            // Campos de Fluxo de Trabalho (Workflow)
            $table->string('status')->default('escrito'); // escrito, aprovado, reprovado
            $table->string('motivo_reprovacao')->nullable();

            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conteudos');
    }
};