<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * Agente Julia: Responsável por coletar dados financeiros (simulados).
 */
class JuliaService
{

    /**
     * URL base da API externa e gratuíta para coleta de dados de ações (US e B3).
     */
    protected $apiBaseUrl = 'https://brapi.dev/api/quote/';

    /**
     * Coleta dados financeiros para o ticker
     * @param string $ticker 
     * @return array Dados estruturados do financeiro
     */
    public function collectFinancialData(string $ticker): array
    {
        //1. Tenta buscar dadso reais da API brapi.dev
        try {
            // A chamada não requer chave de API
            $response = Http::timeout(10)->get($this->apiBaseUrl . $ticker);

            if ($response->successful() && isset($response['results'][0])) {
                $data = $response->json()['results'][0];

                //formata os dados reais da API
                $changePercent = $data['regularMarketChangePercent'] ?? 0;
                $dailyChange = ($changePercent >= 0 ? '+' : '') . number_format($changePercent, 2, ',', '.') . '%';
                $volume = $data['regularMarketVolume'] ?? 0;
                $marketVolume = $this->formatVolume($volume);

                return [
                    'ticker' => $ticker,
                    'current_price' => number_format($data['regularMarketPrice'] ?? $this->fallbackMockValue(200), 2, ',', '.'),
                    'daily_change' => $dailyChange,
                    'market_volume' => $marketVolume,
                    // Esta API não fornece 'score', então simulamos este campo
                    'recommendation_score' => $this->fallbackMockScore(), 
                ];
            }
        } catch (\Throwable $e) {
            // Log de erro pode ser adicionado aqui
            \Log::error("brapi.dev API call failed for {$ticker}: " . $e->getMessage());
        }

        return $this->fallbackMockData($ticker);
    }

    /*
     * Formata o volume de mercado em uma string legível
     */
    protected function formatVolume(int $volume): string
    {
        if ($volume > 1000000) {
        return number_format($volume / 1000000, 1, ',', '.') . 'M';
        if ($volume > 1000) {
            return number_format($volume / 1000, 1, ',', '.') . 'K';
        }
        return (string)$volume;
        }
    }

    // Metodos auxiliares para gerar dados simulados (fallback)

    protected function fallbackMockValue(int $base): string {
        return number_format($base + rand(0, 50) + rand(0, 99)/ 100, 2, ',', '.');
    }
    protected function fallbackMockChange(): string {
        $val = number_format(rand(0, 50) / 10, 2, ',', '.');
        return (rand(0,1) == 1 ? '+' : '-') . $val . '%';
    
    }

    protected function fallbackMockVolume(): string {
        return number_format(rand(10, 50) / 10, 1, ',', '.') . 'M';
}
    protected function fallbackMockScore(): float {
        return round(rand(50, 95) / 10, 1);
    }

    protected function fallbackMockData(string $ticker): array
    {
        return [
            'ticker' => $ticker,
            'current_price' => $this->fallbackMockValue(150),
            'daily_change' => $this->fallbackMockChange(),
            'market_volume' => $this->fallbackMockVolume(),
            'recommendation_score' => $this->fallbackMockScore(),
        ];
    }
}