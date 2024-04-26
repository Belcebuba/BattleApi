<?php

namespace App\Http\Controllers;

use App\Events\Buscarpartida;
use App\Events\terminarparida;
use App\Events\toca;
use App\Events\turno;
use App\Models\Movimiento;
use App\Models\Partida;
use App\Models\Tablero;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class MatchController extends Controller
{
    public function CrearPartida()
    {
        $partida= Partida::where('user_id', auth()->id())->where('estado',[0,1])->first();

        if($partida)
        {
            return response()->json(['message' => 'Ya estás en una partida', 'game_id' => $partida]);
        }
        $partida= Partida::where('enemy_id', auth()->id())->where('estado',1)->first();
        if($partida)
        {
            return response()->json(['message' => 'Ya estás en una partida', 'game_id' => $partida]);
        }
        $partida= Partida::where('estado', 0)->whereNull('enemy_id')->first();
        if ($partida) {
            $partida->enemy_id = auth()->id();
            $partida->estado = '1';
            $partida->turno = $partida->enemy_id;
            $partida->save();

            event(new Buscarpartida($partida->id, auth()->id()));
            event(new toca($partida->turno));

            
            $tablero = new Tablero();
            $tablero->registro_id = $partida->id;
            $tablero->user_id = auth()->id();
            $tablero->estado = json_encode($this->generarbarcos());
            $tablero->save();

            return response()->json(['message' => 'eres el jugador 2', 'registro_id' => $tablero]);
        } else {
            $nuevojuego = new Partida();
            $nuevojuego->user_id = auth()->id();
            $nuevojuego->estado = '0';
            $nuevojuego->save();

            $tablero = new Tablero();
            $tablero->registro_id = $nuevojuego->id;
            $tablero->user_id = auth()->id();
            $tablero->estado = json_encode($this->generarbarcos());
            $tablero->save();

            return response()->json(['message' => 'Esperando al segundo jugador', 'tablero' => $nuevojuego]);
        }
    }
    public function movimiento(Request $request, $registroid)
    {
        $partida=Partida::findOrFail($registroid);

        if($partida->estado==2)
        {
            return response()->json(['error'=>'partida ya finalizada'],400);
        }
        if($partida->turno != auth()->id())
        {
            return response()->json(['error'=>'no es tu turni'],403);
        }

        $enemy_id=($partida->user_id==auth()->id())? $partida->enemy_id: $partida->user_id;

        $tableroenemy= Tablero::where('user_id', $enemy_id)->where('registro_id', $registroid)->first();


        if(!$tableroenemy)
        {
            return response()->json(['error' => 'No se encontró el tablero del oponente:', 'oponente' => $enemy_id, $tableroenemy], 400);
        }

        $estadoTablero=json_decode($tableroenemy->estado, true);
        $horizontal= $request->input('horizontal');
        $vertical= $request->input('vertical');
        if (!isset($estadoTablero[$horizontal][$vertical])) {
            return response()->json(['error' => 'Coordenadas inválidas'], 400);
        }
        if ($this->barcohit($estadoTablero, $horizontal, $vertical)) {
            $estadoTablero[$horizontal][$vertical] = 'K';
            $message = '¡Has golpeado un barco!';
            event(new turno($registroid, auth()->id(), $horizontal, $vertical));

        } else {
            $estadoTablero[$horizontal][$vertical] = 'F';
            $message = 'Solo hay agua en esta posición.';
            event(new turno($registroid, auth()->id(), $horizontal, $vertical));


            $partida->turno = ($partida->turno == $partida->user_id) ? $partida->enemy_id : $partida->user_id;
            $partida->save();
            event(new toca($partida->turno));
        }
        $estadoTablero->estado = json_encode($estadoTablero);
        $estadoTablero->save();

        $move = new Movimiento();
        $move->registro_id = $partida->id;
        $move->user_id = auth()->id();
        $move->horizontal = $horizontal;
        $move->vertical = $vertical;
        $move->save();
        event( new turno($registroid, auth()->id(),$horizontal,$vertical ));

        if ($this->allShips($estadoTablero)) {
            $partida->estado = 3;
            $partida->save();

            $ganador = $partida->turno;
            $perdedor = ($ganador == $partida->user_id) ? $partida->enemy_jd : $partida->user_id;

            event(new terminarparida($partida->id, $ganador));

            if ($ganador == auth()->id()) {
                $mensajeganador = '¡Felicidades! Has hundido todos los barcos del oponente. ¡Has ganado!';
                $loseMessage = '¡El oponente ha hundido todos tus barcos! ¡Has perdido!';
            } else {
                $mensajeganador = '¡El oponente ha hundido todos tus barcos! ¡Has perdido!';
                $loseMessage = '¡Felicidades! Has hundido todos los barcos del oponente. ¡Has ganado!';
            }

            return response()->json(['message' => $mensajeganador, 'ganador' => $ganador, 'perdedor' => $perdedor]);


        }

        return response()->json(['message' => $message]);
    }
    private function generarbarcos()
    {
        $rows = 8;
        $cols = 5;

        $estadoTablero = [];
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $cols; $j++) {
                $estadoTablero[$i][$j] = 'A';
            }
        }
        $numShips = 15;
        for ($s = 0; $s < $numShips; $s++) {
            $horizontal = rand(0, $rows - 1);
            $vertical = rand(0, $cols - 1);

            if ($estadoTablero[$horizontal][$vertical] === 'A') {
                $estadoTablero[$horizontal][$vertical] = 'B';
            } else {
                $s--;
            }
        }

        return $estadoTablero;
    }
    private function barcohit($estadoTablero, $horizontal, $vertical)
    {
        return $estadoTablero[$horizontal][$vertical] === 'B';
    }
    private function allShips($estadoTablero)
    {
        foreach ($estadoTablero as $row) {
            foreach ($row as $cell) {
                if ($cell === 'B') {
                    return false;
                }
            }
        }
        return true;
    }
    public function registro()
    {
        $user = auth()->id();

        $partida = Partida::where('estado', 2)
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user)
                    ->orWhere('enemy_id', $user);
            })
            ->get();

        if ($partida->isEmpty()) {
            return response()->json(['message' => 'sin registro de partidas']);
        }

        return response()->json(['partidas' => $partida]);
    }
}
