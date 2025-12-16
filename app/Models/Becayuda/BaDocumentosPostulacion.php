<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaDocumentosPostulacion extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_documentos_postulacion';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'id_postulacion',
        'nombre_documento',
        'ruta_archivo',
        'estado_revision',
        'observacion',
        'id_user_revisor',
    ];

    public $timestamps = true;

    public function postulacion()
    {
        return $this->belongsTo(BaPostulaciones::class, 'id_postulacion', 'id');
    }
}
