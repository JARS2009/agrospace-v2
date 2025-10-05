<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JugadorProgreso extends Model
{
    protected $table = 'jugador_progreso';
    protected $primaryKey = 'id_jugador';
    
    protected $fillable = [
        'nombre',
        'nivel_actual',
        'xp_actual'
    ];

    protected $casts = [
        'nivel_actual' => 'integer',
        'xp_actual' => 'integer',
    ];

    /**
     * Relación con el nivel actual
     */
    public function nivelActual(): BelongsTo
    {
        return $this->belongsTo(Nivel::class, 'nivel_actual', 'id_nivel');
    }

    /**
     * Obtener el siguiente nivel
     */
    public function getSiguienteNivel()
    {
        return Nivel::where('id_nivel', '>', $this->nivel_actual)
                   ->orderBy('id_nivel', 'asc')
                   ->first();
    }

    /**
     * Verificar si puede subir de nivel
     */
    public function puedeSubirNivel(): bool
    {
        $nivelActualData = $this->nivelActual;
        if (!$nivelActualData) {
            return false;
        }

        return $this->xp_actual >= $nivelActualData->experiencia_requerida;
    }

    /**
     * Calcular el progreso hacia el siguiente nivel (porcentaje)
     */
    public function getProgresoSiguienteNivelAttribute(): float
    {
        $siguienteNivel = $this->getSiguienteNivel();
        if (!$siguienteNivel) {
            return 100; // Ya está en el nivel máximo
        }

        $xpRequerida = $siguienteNivel->experiencia_requerida;
        if ($xpRequerida <= 0) {
            return 100;
        }

        return min(100, ($this->xp_actual / $xpRequerida) * 100);
    }

    /**
     * Agregar experiencia al jugador
     */
    public function agregarExperiencia(int $xp): void
    {
        $this->xp_actual += $xp;
        $this->save();

        // Verificar si puede subir de nivel automáticamente
        while ($this->puedeSubirNivel()) {
            $this->subirNivel();
        }
    }

    /**
     * Subir de nivel
     */
    public function subirNivel(): bool
    {
        $siguienteNivel = $this->getSiguienteNivel();
        if (!$siguienteNivel) {
            return false; // Ya está en el nivel máximo
        }

        $this->nivel_actual = $siguienteNivel->id_nivel;
        $this->save();

        return true;
    }

    /**
     * Obtener los desbloqueos disponibles para el nivel actual
     */
    public function getDesbloqueosDisponibles()
    {
        return NivelDesbloqueo::where('id_nivel', '<=', $this->nivel_actual)->get();
    }
}
