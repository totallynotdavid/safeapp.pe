<?php

namespace App\Models;

use Database\Factories\SupervisorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisor extends Model
{
    /** @use HasFactory<SupervisorFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'supervisor';

    protected $primaryKey = 'id_supervisor';

    protected $fillable = ['nombre', 'estado'];

    protected function casts(): array
    {
        return ['estado' => 'integer'];
    }
}
