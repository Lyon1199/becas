<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaFasesConvocatoria extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_fases_convocatoria';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'id_convocatoria',
        'nombre_fase',
        'fecha_inicio',
        'fecha_fin',
        'descripcion',
        'orden',
        'estado',
        'id_user_created',
        'id_user_updated',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
        'estado'       => 'boolean',
        'orden'        => 'int',
    ];

    public $timestamps = true;

    public function convocatoria()
    {
        return $this->belongsTo(BaConvocatorias::class, 'id_convocatoria', 'id');
    }
}
