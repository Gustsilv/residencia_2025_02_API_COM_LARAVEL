<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\DispatchesEvents;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;


if (interface_exists(CastsAttributes::class) && method_exists(CastsAttributes::class, 'afterCommit')) {
    class ConteudoLog extends Model
    {
        use HasFactory;
        
        // Se a sua versão do Laravel suporta, isso garante que o log é escrito após a transação
        use \Illuminate\Foundation\Testing\Concerns\InteractsWithConsole; 
        
        protected $table = 'conteudo_logs';
        public $timestamps = false;
    
        protected $fillable = [
            'conteudo_id',
            'acao',
            'detalhes',
            'user_id',
        ];
    }
} else {
    class ConteudoLog extends Model
    {
        use HasFactory;
        
        protected $table = 'conteudo_logs';
        public $timestamps = false;
    
        protected $fillable = [
            'conteudo_id',
            'acao',
            'detalhes',
            'user_id',
        ];
    }
}