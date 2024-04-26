<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Tablero extends Model
{
    use HasFactory;
    protected $connection='mongodb';
    protected $coleccion='tablero';
    
    protected $fillable=['rgistro_id','user_id','estado'];
    public function juego()
    {
        return $this->belongsTo(Partida::class);
    }

    public function player()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function obtenercelda($horizontal, $vertical)
    {
        return $this->estado[$horizontal][$vertical];
    }
    public function enviarcelda($horizontal, $vertical, $state)
    {
        $this->estado[$horizontal][$vertical]= $state;
        $this->save();
    }
}
