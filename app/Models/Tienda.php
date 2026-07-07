<?php

namespace App\Models;

use Database\Factories\TiendaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    /** @use HasFactory<TiendaFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'tienda';

    protected $primaryKey = 'id_tienda';

    protected $fillable = ['nombre'];
}
