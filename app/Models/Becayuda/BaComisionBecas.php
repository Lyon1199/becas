<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaComisionBecas extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_comision_becas';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin'    => 'datetime',
        'estado'       => 'boolean',
    ];

    public $timestamps = true;

    public function miembros()
    {
        return $this->hasMany(BaComisionMiembros::class, 'id_comision', 'id');
    }
}
