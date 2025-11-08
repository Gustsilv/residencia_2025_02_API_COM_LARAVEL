<?php

namespace App\Services\AI;

use App\Services\AI\JuliaService;
use App\Services\AI\PedroService;
use App\Services\AI\KeyService;

/**
 * AIOrchestratorService: Coordena a sequência de chamadas dos agentes de IA.
 */
class AIOrchestratorService
{
    protected JuliaService $juliaService;
    protected PedroService $pedroService;
    protected KeyService $keyService;

    /**
     * Injeção de dependências dos serviços.
     */
    public function __construct(
        JuliaService $juliaService,
        PedroService $pedroService,
        KeyService $keyService
    ) {
        $this->juliaService = $juliaService;
        $this->pedroService = $pedroService;
        $this->keyService = $keyService;
    }

    /**
     * Executa a orquestração para gerar o conteúdo final para um determinado ticker.
     *
     * @param string $ticker O ativo para o qual o conteúdo deve ser gerado.
     * @return string O texto final gerado pelo Agente Key.
     */
    public function generateContentForTicker(string $ticker): string
        {
            // 1. Chamar Agente Julia (Coleta de Dados)
            $financialData = $this->juliaService->collectFinancialData($ticker);

            // 2. Chamar Agente Pedro (Análise de Sentimento)
            $sentimentData = $this->pedroService->analyzeMarketSentiment($ticker);

            // 3. Chamar Agente Key (Geração do Texto Final)
            $finalContent = $this->keyService->generateFinalText(
            $financialData,
            $sentimentData
            );

            return $finalContent;
        }
}
