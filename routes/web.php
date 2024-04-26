<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});

//ruta para enviar mensajes, se tiene que proporcionar la id de la partida en la url
Route::post('/play/{partidaid}', function(\Illuminate\Http\Request $request, $partidaid)
{
event(new App\Events\Message($request->horizontal,$request->vertical, $partidaid));
return response()->json(['success' => true]);
});

