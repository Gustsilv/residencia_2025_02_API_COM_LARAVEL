<?php

namespace App\Services\AI;

/**
 * Agente Key: Responsável por gerar o texto final (simulado).
 */
class KeyService
{
    /**
     * Simula a geração do texto final combinando os dados dos agentes.
     *
     * @param array $financialData Dados de Julia
     * @param array $sentimentData Dados de Pedro
     * @return string
     */
    public function generateFinalText(array $financialData, array $sentimentData): string
    {
        // Constrói um texto informativo baseado nos dados mockados
        $text = "ANÁLISE DE CONTEÚDO (SIMULADA)\n\n";
        $text .= "O ativo {$financialData['ticker']} está atualmente cotado a R$ {$financialData['price']}, com um volume transacionado de {$financialData['volume']}.\n";
        $text .= "A análise de sentimento (Agente Pedro) indica um humor de mercado '{$sentimentData['sentiment']}' e uma tendência de '{$sentimentData['trend']}'.\n";
        $text .= "Conclusão do Agente Key: A combinação desses fatores sugere que a volatilidade permanecerá alta no curto prazo. Este é um texto mockado de IA.";
        
        return $text;
    }
}
