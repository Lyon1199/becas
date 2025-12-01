<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaComisionMiembros extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_comision_miembros';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'id_comision',
        'id_user',
        'rol',
        'con_voto',
    ];

    protected $casts = [
        'con_voto' => 'boolean',
    ];

    public $timestamps = true;

    public function comision()
    {
        return $this->belongsTo(BaComisionBecas::class, 'id_comision', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'id_user', 'id');
    }
}
