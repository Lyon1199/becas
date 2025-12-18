<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaDocumentosPostulacion extends Model
{
    // OJO: schema.tabla en Postgres
    protected $table = 'becayuda.ba_documentos_postulacion';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id_postulacion',
        'nombre_documento',
        'ruta_archivo',
        'estado_revision',
        'observacion',
        'id_user_revisor',
    ];

    protected $casts = [
        'id_postulacion' => 'integer',
        'id_user_revisor' => 'integer',
    ];
}
