<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaConvocatorias extends Model
{
    protected $connection = 'pgsql';

    // IMPORTANTE: incluir el esquema porque la tabla está en "becayuda"
    protected $table = 'becayuda.ba_convocatorias';

    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;
    public $timestamps    = true;

    protected $fillable = [
        'id_periodo',
        'nombre',
        'descripcion',
        'tipo_convocatoria',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'id_user_created',
        'id_user_updated',
    ];

    protected $casts = [
        'estado'       => 'boolean',
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
    ];
}
