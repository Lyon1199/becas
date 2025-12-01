<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaAsignaciones extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_asignaciones';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'id_postulacion',
        'monto',
        'porcentaje_sbu',
        'fecha_asignacion',
        'resolucion',
        'estado',
        'id_user_created',
        'id_user_updated',
    ];

    protected $casts = [
        'monto'           => 'float',
        'porcentaje_sbu'  => 'float',
        'fecha_asignacion'=> 'datetime',
        'estado'          => 'boolean',
    ];

    public $timestamps = true;

    public function postulacion()
    {
        return $this->belongsTo(BaPostulaciones::class, 'id_postulacion', 'id');
    }

    public function pagos()
    {
        return $this->hasMany(BaPagos::class, 'id_asignacion', 'id');
    }
}
