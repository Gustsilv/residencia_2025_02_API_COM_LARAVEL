<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conteudo extends Model
{
    use HasFactory;

    // Definindo o status inicial sempre 'escrito'
    const STATUS_ESCRITO = 'escrito';
    const STATUS_APROVADO = 'aprovado';
    const STATUS_REPROVADO = 'reprovado';

    // Constantes para rastreamento de logs (Auditoria)
    const ORIGEM_IA      = 'IA';
    const ORIGEM_HUMANO  = 'Humano';

    protected $fillable = [
        'papel',
        'ticker',
        'conteudo',
        'status',
        'motivo_reprovacao',
    ];

    /**
     * Cria um novo registro de log para este Conteúdo.
     * @param string $acao
     * @param string $origem Origem da ação (IA ou Humano).
     * @param string|null $detalhes
     */
    public function registrarLog(string $acao, ?string $detalhes = null): void
    {
        // Obtém o ID do usuário logado (se houver, senão é null)
        $userId = auth()->check() ? auth()->id() : null; 

        // Cria o log de ação
        // Nota: Assumindo que ConteudoLog está importado ou acessível.
        \App\Models\ConteudoLog::create([
            'conteudo_id' => $this->id,
            'acao' => $acao,
            'origem' => $origem,
            'detalhes' => $detalhes,
            'user_id' => $userId,
        ]);
    }

    public function aprovar(): bool
    {
        // Verifica se o status atual permite a aprovação
        if ($this->status !== self::STATUS_ESCRITO) {
            return false;
        }

        // Log antes da alteração
        $this->registrarLog('aprovado', self::ORIGEM_HUMANO, 'Conteúdo aprovado.');
        $this->status = self::STATUS_APROVADO;
        $this->motivo_reprovacao = null; // Limpa o motivo de reprovação, se houver
        return $this->save();
    }

    public function reprovar(string $motivo): bool
    {
        // Verifica se o status atual permite a reprovação
        if ($this->status !== self::STATUS_ESCRITO) {
            return false; // Não pode reprovar se não estiver em 'escrito'
        }

        if (empty($motivo)) {
            // Motivo de reprovação é obrigatório
            return false;
        }

        // Log antes da alteração, incluindo o motivo nos detalhes
        $this->registrarLog('reprovado', self::ORIGEM_HUMANO, 'Conteúdo reprovado. Motivo: ' . $motivo);

        $this->status = self::STATUS_REPROVADO;
        $this->motivo_reprovacao = $motivo;
        return $this->save();
    }

    protected static function boot()
    {
        parent::boot();

        // Regra 1: Status inicial sempre escrito (apenas na criação)
        static::creating(function ($conteudo) {
            if (empty($conteudo->status)) {
                $conteudo->status = self::STATUS_ESCRITO;
            }
        });

        // Log de Criação (Origem IA, pois é disparado pelo ConteudoController@store)
        static::created(function ($conteudo) {
            $conteudo->registrarLog('criado', self::ORIGEM_IA, 'Conteúdo gerado pela IA.');
        });
        
        // CORREÇÃO AUDITORIA DELEÇÃO: Usar 'deleting' (antes da exclusão do DB) e usar a string 'deletado'
        static::deleting(function ($conteudo) {
            $conteudo->registrarLog('deletado', self::ORIGEM_HUMANO, 'Conteúdo excluído.'); 
        });

        // Regra 4: Editar um conteúdo reprovado volta o status para 'escrito' ao salvar
        static::saving(function ($conteudo) {
            if (
                $conteudo->isDirty('conteudo') && // Se o corpo do conteúdo foi alterado
                $conteudo->getOriginal('status') === self::STATUS_REPROVADO
            ) {
                $conteudo->status = self::STATUS_ESCRITO;
                $conteudo->motivo_reprovacao = null; // Limpa o motivo de reprovação
            }
        });
    }
}
