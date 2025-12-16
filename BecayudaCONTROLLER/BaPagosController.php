<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaPagos extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'becayuda.ba_pagos';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_asignacion',
        'valor_pagado',
        'fecha_pago',
        'estado_pago',
        'observacion',
        'id_user_created',
    ];

    protected $casts = [
        'valor_pagado' => 'decimal:2',
        'fecha_pago'   => 'date:Y-m-d',
    ];

    public function asignacion()
    {
        return $this->belongsTo(BaAsignaciones::class, 'id_asignacion', 'id');
    }
}
