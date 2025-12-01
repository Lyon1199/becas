<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;

class BaBeneficios extends Model
{
    protected $connection = 'pgsql';
    protected $table      = 'becayuda.ba_beneficios';
    protected $primaryKey = 'id';
    protected $keyType    = 'int';
    public $incrementing  = true;

    protected $fillable = [
        'descripcion_sistema',
        'descripcion_senescyt',
        'tipo_beneficio',
        'requisitos_unicos',
        'estado',
        'id_user_created',
        'id_user_updated',
    ];

    protected $casts = [
        'estado'           => 'boolean',
        'requisitos_unicos'=> 'array', // si en BD es JSON; si es texto cambia a string
    ];

    public $timestamps = true;

    public function requisitos()
    {
        return $this->hasMany(
            BaRequisitosBeneficios::class,
            'id_beneficio',
            'id'
        );
    }

    public function postulaciones()
    {
        return $this->hasMany(
            BaPostulaciones::class,
            'id_beneficio',
            'id'
        );
    }
}
