<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaPostulaciones extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_postulaciones';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'id_persona',
        'id_beneficio',
        'id_convocatoria',
        'fecha_postulacion',
        'estado_postulacion',
        'promedio',
        'observaciones',
        'id_user_created',
        'id_user_updated',
    ];

    protected $casts = [
        'fecha_postulacion' => 'datetime',
        'promedio'          => 'float',
    ];

    public $timestamps = true;

    // ----------------- RELACIONES -----------------

    public function beneficio()
    {
        return $this->belongsTo(
            BaBeneficios::class,
            'id_beneficio',
            'id'
        );
    }

    public function convocatoria()
    {
        return $this->belongsTo(
            BaConvocatorias::class,
            'id_convocatoria',
            'id'
        );
    }

    // Usa la tabla public.users como "personas"
    public function persona()
    {
        return $this->belongsTo(
            \App\Models\User::class,
            'id_persona',
            'id'
        );
    }

    public function documentos()
    {
        return $this->hasMany(
            BaDocumentosPostulacion::class,
            'id_postulacion',
            'id'
        );
    }

    public function informes()
    {
        return $this->hasMany(
            BaInformesTecnicos::class,
            'id_postulacion',
            'id'
        );
    }

    public function asignaciones()
    {
        return $this->hasMany(
            BaAsignaciones::class,
            'id_postulacion',
            'id'
        );
    }
}
