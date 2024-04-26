<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Jenssegers\Mongodb\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'movimientos';
    protected $fillable = ['registro_id', 'user_id', 'horizontal', 'vertical'];
}
