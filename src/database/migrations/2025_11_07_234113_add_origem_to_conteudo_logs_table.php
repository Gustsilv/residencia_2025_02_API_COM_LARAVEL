<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrigemToConteudoLogsTable extends Migration
{
    public function up(): void
    {
        Schema::table('conteudo_logs', function (Blueprint $table) {
            $table->string('origem')->default('humano'); // ou nullable se quiser
        });
    }

    public function down(): void
    {
        Schema::table('conteudo_logs', function (Blueprint $table) {
            $table->dropColumn('origem');
        });
    }
}
