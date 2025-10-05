<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nivel extends Model
{
    protected $table = 'niveles';
    protected $primaryKey = 'id_nivel';
    
    protected $fillable = [
        'nombre',
        'experiencia_requerida',
        'descripcion'
    ];

    protected $casts = [
        'experiencia_requerida' => 'integer',
    ];

    /**
     * Relación con los desbloqueos del nivel
     */
    public function desbloqueos(): HasMany
    {
        return $this->hasMany(NivelDesbloqueo::class, 'id_nivel', 'id_nivel');
    }

    /**
     * Relación con los jugadores que están en este nivel
     */
    public function jugadores(): HasMany
    {
        return $this->hasMany(JugadorProgreso::class, 'nivel_actual', 'id_nivel');
    }
}
