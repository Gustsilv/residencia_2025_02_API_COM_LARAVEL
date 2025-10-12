<?php

namespace App\Http\Controllers;

use App\Models\Conteudo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

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

        $perPage = $request->input('per_page', 10);

        $conteudos = $query->paginate($perPage);

        return response()->json($conteudos, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    $validatedData = $request->validate([
        'papel' => 'required|string|max:255',
        'conteudo' => 'required|string',
    ]);

   
    $conteudo = Conteudo::create($validatedData); 

    return response()->json($conteudo, 201);
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
        if ($conteudo->aprovar()) {
            return response()->json($conteudo, 200);
        } 
        return response()->json(['message' => 'Conteúdo não pode ser aprovado. Status atual: ' . $conteudo->status], 400);
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
        if ($conteudo->reprovar($motivo)) {
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
