<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Mostrar el dashboard con la información del jugador
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();
        $progreso = $user->getOrCreateProgresoJuego();
        $nivelActual = $progreso->nivelActual;
        $siguienteNivel = $progreso->getSiguienteNivel();
        
        return Inertia::render('Dashboard', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'land_area_name' => $user->land_area_name,
                'land_area_description' => $user->land_area_description,
                'land_area_size' => $user->land_area_size,
                'land_area_coordinates' => $user->land_area_coordinates,
            ],
            'progreso' => [
                'nombre' => $progreso->nombre,
                'nivel_actual' => $progreso->nivel_actual,
                'xp_actual' => $progreso->xp_actual,
                'progreso_siguiente_nivel' => $progreso->progreso_siguiente_nivel,
                'puede_subir_nivel' => $progreso->puedeSubirNivel(),
            ],
            'nivel_actual' => $nivelActual ? [
                'id' => $nivelActual->id_nivel,
                'nombre' => $nivelActual->nombre,
                'descripcion' => $nivelActual->descripcion,
                'experiencia_requerida' => $nivelActual->experiencia_requerida,
            ] : null,
            'siguiente_nivel' => $siguienteNivel ? [
                'id' => $siguienteNivel->id_nivel,
                'nombre' => $siguienteNivel->nombre,
                'descripcion' => $siguienteNivel->descripcion,
                'experiencia_requerida' => $siguienteNivel->experiencia_requerida,
            ] : null,
            'desbloqueos' => $progreso->getDesbloqueosDisponibles(),
        ]);
    }
}