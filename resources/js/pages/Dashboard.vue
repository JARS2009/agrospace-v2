<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, LogOut, Star, TrendingUp, User } from 'lucide-vue-next';
import { ref } from 'vue';
import FarmScene3D from '../components/FarmScene3D.vue';

interface User {
    id: number;
    name: string;
    email: string;
    land_area_name?: string;
    land_area_description?: string;
    land_area_size?: number;
    land_area_coordinates?: Array<{ lat: number; lng: number }> | string;
}

interface Progreso {
    nombre: string;
    nivel_actual: number;
    xp_actual: number;
    progreso_siguiente_nivel: number;
    puede_subir_nivel: boolean;
}

interface Nivel {
    id: number;
    nombre: string;
    descripcion: string;
    experiencia_requerida: number;
}

interface Props {
    user: User;
    progreso: Progreso;
    nivel_actual: Nivel | null;
    siguiente_nivel: Nivel | null;
    desbloqueos: any[];
}

const props = defineProps<Props>();

// Función para cerrar sesión
const logout = () => {
    router.post('/logout');
};

// Estados reactivos
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const selectedCard = ref<number | null>(null);

// Datos de las tarjetas informativas
const infoCards = ref([
    {
        id: 1,
        title: 'Clima Actual',
        icon: '🌤️',
        summary: 'Soleado, 24°C',
        details:
            'Condiciones ideales para el crecimiento. Humedad: 65%, Viento: 12 km/h del este.',
    },
    {
        id: 2,
        title: 'Estado del Suelo',
        icon: '🌱',
        summary: 'Óptimo',
        details:
            'pH: 6.8, Nutrientes balanceados, Humedad del suelo: 45%. Listo para siembra.',
    },
    {
        id: 3,
        title: 'Cultivos Activos',
        icon: '🌾',
        summary: '12 parcelas',
        details:
            'Maíz: 4 parcelas (75% crecimiento), Tomates: 5 parcelas (60% crecimiento), Lechugas: 3 parcelas (90% crecimiento).',
    },
    {
        id: 4,
        title: 'Recursos',
        icon: '💧',
        summary: 'Disponibles',
        details:
            'Agua: 850L, Semillas: 45 paquetes, Fertilizante: 12kg, Herramientas: 8 disponibles.',
    },
]);

// Función para seleccionar tarjeta
const selectCard = (cardId: number) => {
    selectedCard.value = selectedCard.value === cardId ? null : cardId;
};
</script>

<template>
    <Head title="Dashboard - AgroSpace" />

    <div
        class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-amber-50"
    >
        <!-- Header con información del jugador -->
        <header
            class="border-b border-green-200 bg-white/80 shadow-sm backdrop-blur-sm"
        >
            <div class="mx-auto max-w-full px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between">
                    <!-- Logo/Título -->
                    <div class="flex items-center">
                        <h1
                            class="font-fredoka text-2xl font-bold text-green-800"
                        >
                            AgroSpace
                        </h1>
                    </div>

                    <!-- Información del jugador -->
                    <div class="flex items-center space-x-6">
                        <!-- Nivel y Experiencia -->
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <Star class="h-5 w-5 text-yellow-500" />
                                <span
                                    class="font-fredoka font-semibold text-green-800"
                                >
                                    Nivel {{ progreso.nivel_actual }}
                                </span>
                            </div>

                            <div class="flex items-center space-x-2">
                                <TrendingUp class="h-5 w-5 text-blue-500" />
                                <div class="flex flex-col">
                                    <span
                                        class="font-fredoka text-sm font-medium text-gray-600"
                                    >
                                        {{ progreso.xp_actual }}/{{
                                            siguiente_nivel?.experiencia_requerida || progreso.xp_actual
                                        }}
                                        XP
                                    </span>
                                    <div
                                        class="h-2 w-24 overflow-hidden rounded-full bg-gray-200"
                                    >
                                        <div
                                            class="h-full bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-300"
                                            :style="{
                                                width: `${progreso.progreso_siguiente_nivel}%`,
                                            }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selector de Fecha -->
                        <div class="flex items-center space-x-2">
                            <Calendar class="h-5 w-5 text-green-600" />
                            <input
                                v-model="selectedDate"
                                type="date"
                                class="font-fredoka rounded-lg border border-green-200 px-3 py-2 text-sm font-medium text-green-800 focus:border-green-400 focus:ring-2 focus:ring-green-200 focus:outline-none"
                            />
                        </div>

                        <!-- Opciones de usuario -->
                        <div class="flex items-center space-x-3">
                            <Link
                                href="/profile"
                                class="font-fredoka flex items-center space-x-2 rounded-lg bg-green-100 px-3 py-2 font-semibold text-green-800 transition-colors hover:bg-green-200"
                            >
                                <User class="h-4 w-4" />
                                <span>Perfil</span>
                            </Link>

                            <button
                                @click="logout"
                                class="font-fredoka flex items-center space-x-2 rounded-lg bg-red-100 px-3 py-2 font-semibold text-red-800 transition-colors hover:bg-red-200"
                            >
                                <LogOut class="h-4 w-4" />
                                <span>Salir</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido Principal -->
        <main class="mx-auto max-w-full px-4 py-2">
            <div class="grid h-[calc(100vh-14rem)] grid-cols-12 gap-6">
                <!-- Área del Terreno 3D (9/12) -->
                <div class="col-span-9">
                    <div
                        class="h-full rounded-2xl bg-gradient-to-br from-green-100 to-emerald-100 p-2 shadow-xl"
                    >
                        <!-- Three.js Farm Scene -->
                        <FarmScene3D />
                    </div>
                </div>

                <!-- Panel de Información (3/12) -->
                <div class="col-span-3 flex flex-col space-y-4">
                    <!-- Tarjetas Informativas -->
                    <div class="flex-1 space-y-3">
                        <h3
                            class="font-fredoka mb-3 text-lg font-bold text-green-800"
                        >
                            Información de la Granja
                        </h3>

                        <div class="space-y-2">
                            <div
                                v-for="card in infoCards"
                                :key="card.id"
                                @click="selectCard(card.id)"
                                class="cursor-pointer rounded-xl border-l-4 bg-white p-4 shadow-md transition-all duration-200 hover:shadow-lg"
                                :class="
                                    selectedCard === card.id
                                        ? 'border-green-500 bg-green-50'
                                        : 'border-gray-200 hover:border-green-300'
                                "
                            >
                                <div class="flex items-center space-x-3">
                                    <span class="text-2xl">{{
                                        card.icon
                                    }}</span>
                                    <div class="flex-1">
                                        <h4
                                            class="font-fredoka font-semibold text-gray-800"
                                        >
                                            {{ card.title }}
                                        </h4>
                                        <p
                                            class="font-fredoka text-sm text-gray-600"
                                        >
                                            {{ card.summary }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Información expandida -->
                                <div
                                    v-if="selectedCard === card.id"
                                    class="mt-3 border-t border-green-200 pt-3"
                                >
                                    <p
                                        class="font-fredoka text-sm text-gray-700"
                                    >
                                        {{ card.details }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Secciones Inferiores - Estilo Minecraft -->
            <div class="mt-2 grid grid-cols-1 gap-3 lg:grid-cols-12">
                <!-- Sección de Plantas -->
                <div
                    class="rounded-xl bg-white/90 p-2 shadow-md backdrop-blur-sm lg:col-span-5"
                >
                    <h3
                        class="font-fredoka mb-2 flex items-center text-sm font-bold text-green-800"
                    >
                        <span class="mr-2">🌱</span>
                        Plantas Disponibles
                    </h3>

                    <div class="flex gap-2 overflow-x-auto px-1 py-1 pb-2">
                        <button
                            class="font-fredoka flex h-15 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-green-200 bg-green-100 transition-all hover:scale-105 hover:border-green-300 hover:bg-green-200"
                        >
                            <div class="text-lg">🌽</div>
                            <span class="text-xs font-medium text-green-800"
                                >Maíz</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-red-200 bg-red-100 transition-all hover:scale-105 hover:border-red-300 hover:bg-red-200"
                        >
                            <div class="text-lg">🍅</div>
                            <span class="text-xs font-medium text-red-800"
                                >Tomate</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-green-200 bg-green-100 transition-all hover:scale-105 hover:border-green-300 hover:bg-green-200"
                        >
                            <div class="text-lg">🥬</div>
                            <span class="text-xs font-medium text-green-800"
                                >Lechuga</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-orange-200 bg-orange-100 transition-all hover:scale-105 hover:border-orange-300 hover:bg-orange-200"
                        >
                            <div class="text-lg">🥕</div>
                            <span class="text-xs font-medium text-orange-800"
                                >Zanahoria</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-yellow-200 bg-yellow-100 transition-all hover:scale-105 hover:border-yellow-300 hover:bg-yellow-200"
                        >
                            <div class="text-lg">🌻</div>
                            <span class="text-xs font-medium text-yellow-800"
                                >Girasol</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-pink-200 bg-pink-100 transition-all hover:scale-105 hover:border-pink-300 hover:bg-pink-200"
                        >
                            <div class="text-lg">🌸</div>
                            <span class="text-xs font-medium text-pink-800"
                                >Flores</span
                            >
                        </button>
                    </div>
                </div>

                <!-- Sección de Tipos de Riego -->
                <div
                    class="rounded-xl bg-white/90 p-2 shadow-md backdrop-blur-sm lg:col-span-5"
                >
                    <h3
                        class="font-fredoka mb-2 flex items-center text-sm font-bold text-blue-800"
                    >
                        <span class="mr-2">💧</span>
                        Tipos de Riego
                    </h3>

                    <div class="flex gap-2 overflow-x-auto px-1 py-1 pb-2">
                        <button
                            class="font-fredoka flex h-15 w-20 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-blue-200 bg-blue-100 transition-all hover:scale-105 hover:border-blue-300 hover:bg-blue-200"
                        >
                            <div class="text-lg">🚿</div>
                            <span
                                class="text-center text-xs font-medium text-blue-800"
                                >Aspersión</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-20 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-cyan-200 bg-cyan-100 transition-all hover:scale-105 hover:border-cyan-300 hover:bg-cyan-200"
                        >
                            <div class="text-lg">💧</div>
                            <span
                                class="text-center text-xs font-medium text-cyan-800"
                                >Goteo</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-20 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-teal-200 bg-teal-100 transition-all hover:scale-105 hover:border-teal-300 hover:bg-teal-200"
                        >
                            <div class="text-lg">🌊</div>
                            <span
                                class="text-center text-xs font-medium text-teal-800"
                                >Inundación</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-20 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-indigo-200 bg-indigo-100 transition-all hover:scale-105 hover:border-indigo-300 hover:bg-indigo-200"
                        >
                            <div class="text-lg">⚡</div>
                            <span
                                class="text-center text-xs font-medium text-indigo-800"
                                >Automático</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-20 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-emerald-200 bg-emerald-100 transition-all hover:scale-105 hover:border-emerald-300 hover:bg-emerald-200"
                        >
                            <div class="text-lg">🌿</div>
                            <span
                                class="text-center text-xs font-medium text-emerald-800"
                                >Natural</span
                            >
                        </button>
                    </div>
                </div>

                <!-- Sección de Acciones Especiales -->
                <div
                    class="rounded-xl bg-white/90 p-2 shadow-md backdrop-blur-sm lg:col-span-2"
                >
                    <h3
                        class="font-fredoka mb-2 flex items-center text-sm font-bold text-amber-800"
                    >
                        <span class="mr-2">⚙️</span>
                        Acciones Especiales
                    </h3>

                    <div class="flex gap-2 overflow-x-auto px-1 py-1 pb-2">
                        <button
                            class="font-fredoka flex h-15 w-24 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-amber-200 bg-amber-100 transition-all hover:scale-105 hover:border-amber-300 hover:bg-amber-200"
                        >
                            <div class="text-lg">🚜</div>
                            <span
                                class="text-center text-xs font-medium text-amber-800"
                                >Preparar Terreno</span
                            >
                        </button>

                        <button
                            class="font-fredoka flex h-15 w-24 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 border-violet-200 bg-violet-100 transition-all hover:scale-105 hover:border-violet-300 hover:bg-violet-200"
                        >
                            <div class="text-lg">⏩</div>
                            <span
                                class="text-center text-xs font-medium text-violet-800"
                                >Adelantar Producción</span
                            >
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap');

.font-fredoka {
    font-family: 'Fredoka', sans-serif;
}
</style>
