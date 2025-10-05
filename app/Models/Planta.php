<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planta extends Model
{
    protected $table = 'plantas';
    protected $primaryKey = 'id_planta';
    
    protected $fillable = [
        'nombre',
        'tipo',
        'tiempo_crecimiento',
        'valor'
    ];

    protected $casts = [
        'tiempo_crecimiento' => 'integer',
        'valor' => 'integer',
    ];

    /**
     * Obtener el tiempo de crecimiento en formato legible
     */
    public function getTiempoCrecimientoFormateadoAttribute(): string
    {
        if (!$this->tiempo_crecimiento) {
            return 'No definido';
        }

        $horas = floor($this->tiempo_crecimiento / 3600);
        $minutos = floor(($this->tiempo_crecimiento % 3600) / 60);
        $segundos = $this->tiempo_crecimiento % 60;

        $formato = [];
        if ($horas > 0) $formato[] = "{$horas}h";
        if ($minutos > 0) $formato[] = "{$minutos}m";
        if ($segundos > 0) $formato[] = "{$segundos}s";

        return implode(' ', $formato);
    }
}
