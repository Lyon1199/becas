<?php

namespace App\Models\Becayuda;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaBeneficios extends Model
{
    use HasFactory;

    // Tabla con esquema
    protected $table = 'becayuda.ba_beneficios';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'descripcion_sistema',
        'descripcion_senescyt',
        'tipo_beneficio',
        'requisitos_unicos',
        'estado',
        'id_user_created',
        'id_user_updated',
    ];

    // requisitos_unicos se guarda como número (en la BD es jsonb pero solo guardamos 9, 9.5, etc.)
    protected $casts = [
        'requisitos_unicos' => 'float',
        'estado'            => 'boolean',
    ];

    // No mostrar la columna cruda en el JSON
    protected $hidden = [
        'requisitos_unicos',
    ];

    // Campos calculados que sí se devuelven al frontend
    protected $appends = [
        'promedio_minimo',
        'mensaje_requisito',
    ];

    // Devuelve el número de promedio mínimo
    public function getPromedioMinimoAttribute()
    {
        return $this->requisitos_unicos;
    }

    // Devuelve el mensaje "Promedio mínimo X"
    public function getMensajeRequisitoAttribute()
    {
        if ($this->requisitos_unicos === null) {
            return null;
        }

        // Formateo bonito: 9 o 9.5 (sin muchos decimales feos)
        $valor = rtrim(rtrim(number_format($this->requisitos_unicos, 2, '.', ''), '0'), '.');

        return 'Promedio mínimo ' . $valor;
    }
}
