<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaDirectores extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_directores';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'nombres',
        'cargo',
        'estado',
        'id_user_created',
        'id_user_updated',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public $timestamps = true;
}
