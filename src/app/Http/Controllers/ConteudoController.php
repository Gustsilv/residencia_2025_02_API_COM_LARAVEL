<?php

namespace App\Http\Controllers;

use App\Models\Conteudo;
use Illuminate\Http\Request;
use App\Http\Requests\ConteudoRequest;
use App\Services\AI\AIOrchestratorService;
use App\Models\ConteudoLog; // Importar para auditoria
use Illuminate\Http\Response;

class ConteudoController extends Controller
{
    /**
     * Display a listing of the resource.
     *  
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Conteudo::query();

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('papel')) {
            $query->where('papel', $request->input('papel'));
        }

        // Aplica o filtro 'ticker'        
        if ($request->filled('ticker')) {
            $query->where('ticker', $request->input('ticker'));
        }


        $perPage = $request->input('per_page', 10);

        $conteudos = $query->paginate($perPage);

        return response()->json($conteudos, 200);
    }

    /**
     * create: Cria o conteúdo com status = escrito, chamando a orquestração da IA.
     * POST /conteudos
     * * @param  \App\Http\Requests\ConteudoRequest  $request
     * @param  \App\Services\AI\AIOrchestratorService $orchestratorService
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ConteudoRequest $request, AIOrchestratorService $orchestratorService)
    {
        $validatedData = $request->validated();
        $ticker = $validatedData['ticker'];

        try {
            // 1. CHAMA O ORQUESTRADOR: Recebe o texto gerado pelos 3 agentes
            $generatedContent = $orchestratorService->generateContentForTicker($ticker);

            // 2. Cria o registro no DB (status 'escrito' é default no Model)
            $conteudo = Conteudo::create([
                'papel' => $validatedData['papel'],
                'conteudo' => $generatedContent,
                'status' => Conteudo::STATUS_ESCRITO,
                'ticker' => $ticker, // Salva o ticker
            ]);
            // Retorna Json com o texto gerado e status inicial "Escrito"
            return response()->json($conteudo, 201); // Resposta 201 Created

        } catch (\Exception $e) {
            // Em caso de falha de API ou Orquestrador
            return response()->json([
                'message' => 'Falha na orquestração da IA ou na API externa.',
                'error_detail' => $e->getMessage()
            ], 500); // Resposta 500 Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $conteudo = Conteudo::findOrFail($id);

        return response()->json($conteudo, 200);
    }


    /**
     * Aprova o conteúdo especificado.
     * POST /conteudos/{id}/aprovar
     * @param  \App\Models\Conteudo  $conteudo
     * @return \Illuminate\Http\JsonResponse
     */
    public function aprovar(Conteudo $conteudo)
    {
        // Lógica de auditoria será tratada pelo Model::aprovar() com origem 'Humano'
        if ($conteudo->aprovar(ConteudoLog::ORIGEM_HUMANO)) {
            return response()->json($conteudo, 200); // Resposta 200 OK
        }

        // Se o método aprovar() falhar (status != escrito)
        return response()->json([
            'message' => 'Conteúdo não pode ser aprovado. Status atual: ' . $conteudo->status
        ], 400); // Resposta 400 Bad Request
    }

    /**
     * Reprova o conteúdo especificado com um motivo.
     * POST /conteudos/{id}/reprovar
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Conteudo  $conteudo
     * @return \Illuminate\Http\JsonResponse
     */
    public function reprovar(Request $request, Conteudo $conteudo)
    { 
        $request->validate([
            'motivo' => 'required|string|min:10',
        ]);

        $motivo = $request->input('motivo');

        // Tenta reprovar o conteúdo com o motivo fornecido
         // Lógica de auditoria será tratada pelo Model::reprovar() com origem 'Humano'
        if ($conteudo->reprovar($motivo, ConteudoLog::ORIGEM_HUMANO)) {
            return response()->json($conteudo, 200);
        } 
        // Se não for possível reprovar, retorna um erro
        return response()->json(['message' => 'Conteúdo não pode ser reprovado. Status atual: ' . $conteudo->status], 400);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Conteudo  $conteudo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Conteudo $conteudo)
    {
        $conteudo->delete();
        // Resposta 204 No Content (padrão para DELETE sem corpo de resposta)
        return response(null, 204);
    }
}
