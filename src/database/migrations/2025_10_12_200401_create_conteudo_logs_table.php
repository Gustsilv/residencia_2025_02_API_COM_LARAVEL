<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConteudoLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conteudo_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conteudo_id')->constrained('conteudos')->onDelete('cascade');
            $table->string('acao'); // 'aprovado' ou 'reprovado'
            $table->text('detalhes')->nullable(); // Detalhes adicionais, como motivo de reprovação
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); 
            $table->timestamp('data_hora')->useCurrent(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conteudo_logs');
    }
}
