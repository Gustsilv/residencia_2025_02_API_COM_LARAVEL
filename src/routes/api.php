<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Imports de Controllers existentes
use App\Http\Controllers\TokenController;
use App\Http\Controllers\AgendaController;

// Import do Controller de Conteúdo (novo)
use App\Http\Controllers\ConteudoController; 



// Rotas de Agenda (Existentes)
Route::get('/agenda', [AgendaController::class, 'index']);      // 200
Route::post('/agenda', [AgendaController::class, 'criar']);     // 201
Route::get('/agenda/{id}', [AgendaController::class, 'visualizar']); // 200 ou 404
Route::put('/agenda/{id}', [AgendaController::class, 'atualizar']); // 200 ou 404
Route::delete('/agenda/{id}', [AgendaController::class, 'deletar']); // 200 ou 404



// Rota de Token (Existente)
Route::post('/user', [TokenController::class, 'index']); // 200 ou 401


// Rotas de Conteúdo (Atualizadas e Otimizadas)
// Rotas RESTful Padrão: index, store, show, destroy
Route::apiResource('conteudos', ConteudoController::class)->only([
    'index', 'store', 'show', 'destroy'
]);

// Rotas de Transição de Status (Usando {conteudo} para Route Model Binding e consistência)
Route::post('conteudos/{conteudo}/aprovar', [ConteudoController::class, 'aprovar']);    // 200 ou 400 ou 404
Route::post('conteudos/{conteudo}/reprovar', [ConteudoController::class, 'reprovar']);    // 200 ou 400 ou 404


Route::group(['middleware' => ['JWTToken']], function () {
    // Route::get('/agenda', [AgendaController::class, 'index']);  //200
    Route::get('/conteudos/pendentes', [ConteudoController::class, 'pendentes']);
// Defiindo rota de endpoint de listar pendentes 

});