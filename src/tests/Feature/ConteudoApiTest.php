<?php

namespace Tests\Feature;

use App\Models\Conteudo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConteudoApiTest extends TestCase
{
    // Limpa o banco de dados antes de cada teste
    use RefreshDatabase; 

    /**
     * Teste: POST /conteudos
     * Deve criar um novo conteúdo com status inicial 'escrito' e logar a ação.
     */
    public function test_pode_criar_um_novo_conteudo_e_o_status_inicial_e_escrito(): void
    {
        $data = [
            'papel' => 'redator',
            'conteudo' => 'Este é um rascunho de um novo post.',
        ];

        $response = $this->postJson('/api/conteudos', $data);

        $response
            ->assertStatus(201) // 201 Created
            ->assertJson([
                'papel' => 'redator',
                'status' => Conteudo::STATUS_ESCRITO,
            ]);

        // Verifica o log de auditoria
        $this->assertDatabaseHas('conteudo_logs', [
            'acao' => 'criado',
            'conteudo_id' => $response->json('id'),
        ]);
    }

    /**
     * Teste: GET /conteudos/{id}
     * Deve retornar 404 para um ID que não existe.
     */
    public function test_nao_pode_visualizar_conteudo_inexistente(): void
    {
        $response = $this->getJson('/api/conteudos/999'); 
        $response->assertStatus(404); // 404 Not Found
    }

    /**
     * Teste: GET /conteudos/{id}
     * Deve visualizar um conteúdo existente.
     */
    public function test_pode_visualizar_um_conteudo_existente(): void
    {
        // CORRIGIDO: Usando Conteudo::create() para evitar ConteudoFactory
        $conteudo = Conteudo::create(['papel' => 'visualizar', 'conteudo' => 'conteudo de teste']);

        $response = $this->getJson("/api/conteudos/{$conteudo->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'id' => $conteudo->id,
                'papel' => $conteudo->papel,
            ]);
    }
    
    /**
     * Teste: POST /conteudos/{id}/aprovar
     * Deve aprovar um conteúdo com status 'escrito' e logar a ação.
     */
    public function test_pode_aprovar_um_conteudo_com_status_escrito(): void
    {
        $conteudo = Conteudo::create(['papel' => 'editor', 'conteudo' => 'Conteúdo para aprovação.', 'status' => Conteudo::STATUS_ESCRITO]);

        $response = $this->postJson("/api/conteudos/{$conteudo->id}/aprovar");

        $response
            ->assertStatus(200) // 200 OK
            ->assertJson(['status' => Conteudo::STATUS_APROVADO]);

        $this->assertDatabaseHas('conteudo_logs', ['acao' => 'aprovado']);
    }

    /**
     * Teste: Transição Inválida (Regra 2)
     * Não deve aprovar um conteúdo que já está 'aprovado' (Retorna 400).
     */
    public function test_nao_pode_aprovar_um_conteudo_ja_aprovado(): void
    {
        $conteudo = Conteudo::create(['papel' => 'editor', 'conteudo' => 'Conteúdo já aprovado.', 'status' => Conteudo::STATUS_APROVADO]);

        $response = $this->postJson("/api/conteudos/{$conteudo->id}/aprovar");

        $response
            ->assertStatus(400) // 400 Bad Request
            ->assertJsonFragment(['message' => 'Conteúdo não pode ser aprovado. Status atual: aprovado']);
    }

    /**
     * Teste: POST /conteudos/{id}/reprovar
     * Deve reprovar com sucesso e logar a ação.
     */
    public function test_pode_reprovar_um_conteudo_com_status_escrito_e_motivo_valido(): void
    {
        $conteudo = Conteudo::create(['papel' => 'editor', 'conteudo' => 'Conteúdo para reprovação.', 'status' => Conteudo::STATUS_ESCRITO]);

        $motivo = 'O conteúdo não atende aos critérios de qualidade.';
        $data = ['motivo' => $motivo];

        $response = $this->postJson("/api/conteudos/{$conteudo->id}/reprovar", $data);

        $response
            ->assertStatus(200)
            ->assertJson(['status' => Conteudo::STATUS_REPROVADO]);

        $this->assertDatabaseHas('conteudo_logs', [
            'acao' => 'reprovado',
            'detalhes' => 'Conteúdo reprovado. Motivo: ' . $motivo,
        ]);
    }

    /**
     * Teste: Validação de Reprovação (Regra 3)
     * Não deve reprovar sem fornecer o motivo (Retorna 422).
     */
    public function test_nao_pode_reprovar_sem_fornecer_o_motivo(): void
    {
        $conteudo = Conteudo::create(['papel' => 'editor', 'conteudo' => 'Conteúdo para reprovação sem motivo.', 'status' => Conteudo::STATUS_ESCRITO]);

        $response = $this->postJson("/api/conteudos/{$conteudo->id}/reprovar", ['motivo' => '']);

        $response->assertStatus(422); // 422 Unprocessable Entity
    }

    /**
     * Teste: Regra de Edição (Regra 4)
     * Editar um conteúdo 'reprovado' deve resetar o status para 'escrito'.
     */
    public function test_editar_um_conteudo_reprovado_volta_o_status_para_escrito(): void
    {
        $conteudo = Conteudo::create([
            'papel' => 'editor',
            'conteudo' => 'Conteúdo inicialmente reprovado.',
            'status' => Conteudo::STATUS_REPROVADO,
            'motivo_reprovacao' => 'Motivo inicial de reprovação.',
        ]);

        $data = [
            'papel' => $conteudo->papel,
            'conteudo' => 'Conteúdo editado após reprovação.', 
        ];

        // Simula a edição
        $conteudo->fill($data);
        $conteudo->save();

        // Requisita o recurso para verificar o status
        $response = $this->getJson("/api/conteudos/{$conteudo->id}");

        $response
            ->assertStatus(200)
            ->assertJson([
                'conteudo' => 'Conteúdo editado após reprovação.',
                'status' => Conteudo::STATUS_ESCRITO, 
                'motivo_reprovacao' => null, 
            ]);
    }

    /**
     * Teste: DELETE /conteudos/{id}
     * Deve deletar um conteúdo e retornar 204 No Content.
     */
    public function test_pode_deletar_um_conteudo_e_retorna_204(): void
    {
        // CORRIGIDO: Usando Conteudo::create() para evitar ConteudoFactory
        $conteudo = Conteudo::create(['papel' => 'teste', 'conteudo' => 'conteudo a deletar']);

        $response = $this->deleteJson("/api/conteudos/{$conteudo->id}");

        $response->assertStatus(204); 

        $this->assertDatabaseMissing('conteudos', ['id' => $conteudo->id]);

        $this->assertDatabaseHas('conteudo_logs', [
            'acao' => 'deletado',
            'conteudo_id' => $conteudo->id,
        ]);
    }
}