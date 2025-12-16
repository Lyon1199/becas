<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaAsignaciones extends Model
{
    use HasFactory;

    protected $connection = 'pgsql';
    protected $table = 'becayuda.ba_asignaciones';
    protected $primaryKey = 'id';
    public $timestamps = true;

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
        'monto' => 'decimal:2',
        'porcentaje_sbu' => 'decimal:2',
        'fecha_asignacion' => 'date:Y-m-d',
    ];

    public function postulacion()
    {
        return $this->belongsTo(BaPostulaciones::class, 'id_postulacion', 'id');
    }
}
