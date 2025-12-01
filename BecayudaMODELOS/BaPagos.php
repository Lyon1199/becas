<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaPagos extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_pagos';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'id_asignacion',
        'valor_pagado',
        'fecha_pago',
        'estado_pago',
        'observacion',
        'id_user_created',
    ];

    protected $casts = [
        'valor_pagado' => 'float',
        'fecha_pago'   => 'datetime',
    ];

    public $timestamps = true;

    public function asignacion()
    {
        return $this->belongsTo(BaAsignaciones::class, 'id_asignacion', 'id');
    }
}
