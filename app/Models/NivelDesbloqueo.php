<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NivelDesbloqueo extends Model
{
    protected $table = 'nivel_desbloqueos';
    protected $primaryKey = 'id_desbloqueo';
    
    protected $fillable = [
        'id_nivel',
        'tipo_desbloqueo',
        'id_elemento'
    ];

    protected $casts = [
        'id_nivel' => 'integer',
        'id_elemento' => 'integer',
    ];

    /**
     * Relación con el nivel
     */
    public function nivel(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'id_nivel', 'id_nivel');
    }

    /**
     * Obtener el elemento desbloqueado según su tipo
     */
    public function getElementoDesbloqueado()
    {
        switch ($this->tipo_desbloqueo) {
            case 'planta':
                return Planta::find($this->id_elemento);
            case 'riego':
                return TipoRiego::find($this->id_elemento);
            case 'herramienta':
                // Aquí podrías agregar el modelo de herramientas cuando lo tengas
                return null;
            case 'otro':
                return null;
            default:
                return null;
        }
    }

    /**
     * Verificar si es un desbloqueo de planta
     */
    public function esPlanta(): bool
    {
        return $this->tipo_desbloqueo === 'planta';
    }

    /**
     * Verificar si es un desbloqueo de riego
     */
    public function esRiego(): bool
    {
        return $this->tipo_desbloqueo === 'riego';
    }

    /**
     * Verificar si es un desbloqueo de herramienta
     */
    public function esHerramienta(): bool
    {
        return $this->tipo_desbloqueo === 'herramienta';
    }
}
