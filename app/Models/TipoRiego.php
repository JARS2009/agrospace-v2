<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoRiego extends Model
{
    protected $table = 'tipos_riego';
    protected $primaryKey = 'id_riego';
    
    protected $fillable = [
        'nombre',
        'eficiencia',
        'costo'
    ];

    protected $casts = [
        'eficiencia' => 'decimal:2',
        'costo' => 'integer',
    ];

    /**
     * Obtener el porcentaje de eficiencia
     */
    public function getEficienciaPorcentajeAttribute(): string
    {
        return number_format($this->eficiencia * 100, 0) . '%';
    }

    /**
     * Verificar si es el tipo de riego más eficiente
     */
    public function esMaximaEficiencia(): bool
    {
        return $this->eficiencia >= 1.0;
    }

    /**
     * Calcular el costo por punto de eficiencia
     */
    public function getCostoPorEficienciaAttribute(): float
    {
        if ($this->eficiencia <= 0) {
            return 0;
        }
        
        return round($this->costo / $this->eficiencia, 2);
    }
}
