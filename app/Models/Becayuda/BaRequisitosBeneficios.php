<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaRequisitosBeneficios extends Model
{
    use HasFactory;

    // Tabla con esquema en PostgreSQL
    protected $table = 'becayuda.ba_requisitos_beneficios';

    // NO usamos protected $connection = 'becayuda';
    // Usará la conexión por defecto (pgsql)

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

    // Relación opcional con beneficios (si ya tienes BaBeneficios)
    public function beneficio()
    {
        return $this->belongsTo(BaBeneficios::class, 'id_beneficio');
    }
}
