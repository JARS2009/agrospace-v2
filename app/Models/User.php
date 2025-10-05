<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'land_area_coordinates',
        'land_area_size',
        'land_area_name',
        'land_area_description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'land_area_coordinates' => 'array',
        ];
    }

    /**
     * Relación con el progreso del jugador en el juego
     */
    public function progresoJuego(): HasOne
    {
        return $this->hasOne(JugadorProgreso::class, 'id_jugador');
    }

    /**
     * Obtener o crear el progreso del juego para este usuario
     */
    public function getOrCreateProgresoJuego(): JugadorProgreso
    {
        return $this->progresoJuego()->firstOrCreate([
            'id_jugador' => $this->id,
        ], [
            'nombre' => $this->name,
            'nivel_actual' => 1,
            'xp_actual' => 0,
        ]);
    }

    /**
     * Obtener el nivel actual del jugador
     */
    public function getNivelActualAttribute(): int
    {
        return $this->progresoJuego?->nivel_actual ?? 1;
    }

    /**
     * Obtener la experiencia actual del jugador
     */
    public function getXpActualAttribute(): int
    {
        return $this->progresoJuego?->xp_actual ?? 0;
    }

    /**
     * Agregar experiencia al jugador
     */
    public function agregarExperiencia(int $xp): bool
    {
        $progreso = $this->getOrCreateProgresoJuego();
        return $progreso->agregarExperiencia($xp);
    }

    /**
     * Verificar si el jugador puede subir de nivel
     */
    public function puedeSubirNivel(): bool
    {
        $progreso = $this->progresoJuego;
        return $progreso ? $progreso->puedeSubirNivel() : false;
    }

    /**
     * Obtener los desbloqueos disponibles para el jugador
     */
    public function getDesbloqueosDisponibles(): array
    {
        $progreso = $this->progresoJuego;
        return $progreso ? $progreso->getDesbloqueosDisponibles() : [];
    }

    /**
     * Obtener el progreso hacia el siguiente nivel (0-100)
     */
    public function getProgresoSiguienteNivel(): float
    {
        $progreso = $this->progresoJuego;
        return $progreso ? $progreso->progreso_siguiente_nivel : 0.0;
    }
}
