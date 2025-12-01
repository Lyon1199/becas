<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Model;
use App\Models\Becayuda\BaConvocatorias;

class BaPeriodos extends Model
{
    protected $connection = 'pgsql';
    protected $table = 'becayuda.ba_periodos';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'descripcion',
        'estado',
        'id_user_created',
        'id_user_updated',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public $timestamps = true;

    public function convocatorias()
    {
        return $this->hasMany(
            BaConvocatorias::class,
            'id_periodo',
            'id'
        );
    }
}
