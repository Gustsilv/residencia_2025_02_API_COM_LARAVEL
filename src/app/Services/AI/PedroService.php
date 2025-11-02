<?php

namespace App\Services\AI;

/**
 * Agente Pedro: Responsável por analisar tendências e sentimentos (simulados).
 */
class PedroService
{
    /**
     * Simula a análise de sentimento de mercado.
     *
     * @param string $ticker
     * @return array
     */
    public function analyzeMarketSentiment(string $ticker): array
    {
        $sentiments = ['Bullish', 'Bearish', 'Neutral'];
        $trends = ['High Volatility', 'Consolidation', 'Upward'];

        return [
            'sentiment' => $sentiments[array_rand($sentiments)],
            'trend' => $trends[array_rand($trends)],
            'summary' => 'O mercado está reagindo de forma mista às últimas notícias regulatórias.',
            'confidence' => rand(70, 95) / 100,
        ];
    }
}