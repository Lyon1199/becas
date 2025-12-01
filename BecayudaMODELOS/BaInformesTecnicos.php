<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaInformesTecnicos extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_informes_tecnicos';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'id_postulacion',
        'tipo_informe',
        'resultado',
        'calificacion',
        'fecha_emision',
        'responsable',
    ];

    protected $casts = [
        'calificacion' => 'float',
        'fecha_emision'=> 'datetime',
    ];

    public $timestamps = true;

    public function postulacion()
    {
        return $this->belongsTo(BaPostulaciones::class, 'id_postulacion', 'id');
    }
}
