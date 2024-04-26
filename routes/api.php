<?php

use App\Http\Controllers\MatchController;
use App\Http\Controllers\mecanicasController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebSocketController;
use BeyondCode\LaravelWebSockets\Facades\WebSocketsRouter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
/*
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});*/
Route::post('login',[ UserController::class, 'login']);
Route::post('sendEmail',[ UserController::class, 'sendEmail']);
Route::post('linkac',[ UserController::class, 'linkac']);
Route::get('activar-usuario/{token}', [UserController::class, 'activaruser'])->name('activarUsuario');
Route::post('store',[ UserController::class, 'store']);
Route::post('codeCheck',[ UserController::class, 'codeCheck']);
WebSocketsRouter::webSocket('/ws-api', WebSocketController::class);
Route::post('crearPartida', [MatchController::class, 'crearPartida']);
Route::post('unirsePartida/{partidaid}', [MatchController::class, 'unirsePartida']);
Route::get('generarBarcos', [mecanicasController::class, 'generarBarcos']);

Route::middleware('jwt.verify')->group(function(){
Route::post('logout',[ UserController::class, 'logout']);

});

Route::post('hit/{idgame}', [MatchController::class, 'movimiento']);
Route::post('play', [MatchController::class, 'CrearPartida']);
Route::get('show/{game}', [mecanicasController::class, 'show']);
Route::get('oponente/{game}', [mecanicasController::class, 'vertableronemy']);
Route::get('registro', [MatchController::class, 'registro']);
