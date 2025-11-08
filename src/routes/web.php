<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Noticias_DestaquesController;
use App\Http\Controllers\ConteudoController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::get('/conteudos/revisao', [ConteudoController::class, 'revisao']);

Route::post('/conteudos/{conteudo}/aprovar', [ConteudoController::class, 'aprovar']);
Route::post('/conteudos/{conteudo}/reprovar', [ConteudoController::class, 'reprovar']);


Route::get('/', function () {
    return view('welcome');
});


// Route::get('/cadastrar',[Noticias_DestaquesController::class,'novo']);
