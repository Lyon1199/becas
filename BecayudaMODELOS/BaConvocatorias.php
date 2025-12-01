<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;
use App\Models\Becayuda\BaPeriodos;

class BaConvocatorias extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'becayuda.ba_convocatorias'; // ajusta si tu tabla se llama distinto
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

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
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
    ];

    public $timestamps = true;

    public function periodo()
    {
        return $this->belongsTo(
            BaPeriodos::class,
            'id_periodo',
            'id'
        );
    }
}
