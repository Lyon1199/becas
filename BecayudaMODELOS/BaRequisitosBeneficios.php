<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaRequisitosBeneficios extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_requisitos_beneficios';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'id_beneficio',
        'id_convocatoria',
        'descripcion_requisito',
        'quien_sube_requisito',
        'obligatorio',
        'estado',
        'id_user_created',
        'id_user_updated',
    ];

    protected $casts = [
        'obligatorio' => 'boolean',
        'estado'      => 'boolean',
    ];

    public $timestamps = true;

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
}
