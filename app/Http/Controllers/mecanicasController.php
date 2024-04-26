<?php

namespace App\Http\Controllers;

use App\Models\Partida;
use App\Models\Tablero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class mecanicasController extends Controller
{

    public function show($registroid) {
        $registro=Partida::findOrFail($registroid);
        if($registro->estado==1)
        {
            if($registro->user_id==Auth::id()||$registro->enemy_id==Auth::id())
            {
                $tablero=Tablero::where('user_id', Auth::id())->where('registro_id', $registroid)->first();
                return response()->json(['Tablero'=>$tablero]);
            }
            else{
                return response()->json(['error'=>'Error al mostrar el tablero: no tienes permiso'],400);
            }
        }

    }
    public function vertableronemy($registroid)
    
    {
        $registro=Partida::findOrFail($registroid);
        if($registro->estado==1)
        {
            if($registro->user_id==Auth::id()||$registro->enemy_id==Auth::id())
            {
                $enemyid=($registro->user_id==Auth::id())?$registro->enemyid:$registro->user_id;
                $tableroenmy=Tablero::where('user_id', $enemyid)->where('registro_id',$registroid)->first();
                return response()->json(['Tablero'=>$tableroenmy]);
            }
            else{
                return response()->json(['error'=>'Error al mostrar el tablero: no tienes permiso'],400);
            }
        }
        else
        {
            return response()->json(['error'=>'El juego no esta en curso o finalizo'],404);
        }
    }
}
