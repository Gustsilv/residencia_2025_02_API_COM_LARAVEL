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

    public function pendentes()
    {
        $pendentes = Conteudo::where('status', 'escrito')->get();

        return response()->json($pendentes);
    }

    public function revisao()
    {
        // pega só os pendentes de revisão
        $conteudos = Conteudo::where('status', 'escrito')->get();

        return view('conteudos.revisao', compact('conteudos'));
    }

    /**
     * Aprova o conteúdo especificado, marcando a origem como 'Humana'.
     * POST /conteudos/{id}/aprovar
     *
     * @param \App\Models\Conteudo $conteudo O modelo do Conteúdo a ser aprovado.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a página de revisão em caso de sucesso ou retorna com erro.
     */
    public function aprovar(Conteudo $conteudo)
    {
        if ($conteudo->aprovar(Conteudo::ORIGEM_HUMANO)) {

            session()->flash('sucesso', "Conteúdo ID {$conteudo->id} aprovado com sucesso.");

            return redirect('/conteudos/revisao');
        }

        return back()->with('erro', 'Conteúdo não pode ser aprovado. Status atual: ' . $conteudo->status);
    }
   


    /**
     * Reprova o conteúdo especificado com um motivo fornecido na requisição.
     * POST /conteudos/{id}/reprovar
     *
     * @param \Illuminate\Http\Request $request A requisição HTTP contendo o campo 'motivo'.
     * @param \App\Models\Conteudo $conteudo O modelo do Conteúdo a ser reprovado.
     * @return \Illuminate\Http\RedirectResponse Redireciona para a página de revisão em caso de sucesso ou retorna com erro.
     */
    public function reprovar(Request $request, Conteudo $conteudo)
    {
        $request->validate([
            'motivo' => 'required|string|min:10',
        ]);

        $motivo = $request->input('motivo');

        if ($conteudo->reprovar($motivo, Conteudo::ORIGEM_HUMANO)) {

            session()->flash('sucesso', "Conteúdo ID {$conteudo->id} reprovado com sucesso.");
            return redirect('/conteudos/revisao');
        }

        return back()->with('erro', 'Conteúdo não pode ser reprovado. Status atual: ' . $conteudo->status);
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
