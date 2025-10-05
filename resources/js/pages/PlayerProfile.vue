<script setup lang="ts">
import LandAreaSelector from '@/components/LandAreaSelector.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    MapPin,
    Star,
    Target,
    Trophy,
    User,
    Zap,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

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

// Referencia al componente del mapa
const landAreaSelectorRef = ref(null);

// Computed property para parsear las coordenadas
const parsedCoordinates = computed(() => {
    console.log('=== DEBUGGING COORDINATE PARSING ===');
    console.log('Raw user data:', props.user);
    console.log('Raw land_area_coordinates:', props.user.land_area_coordinates);
    console.log(
        'Type of land_area_coordinates:',
        typeof props.user.land_area_coordinates,
    );

    if (!props.user.land_area_coordinates) {
        console.log('No coordinates found - returning empty array');
        return [];
    }

    let coordinates = props.user.land_area_coordinates;

    // Si es string, parsearlo
    if (typeof coordinates === 'string') {
        console.log('Coordinates is string, attempting to parse...');
        try {
            coordinates = JSON.parse(coordinates);
            console.log('Successfully parsed coordinates:', coordinates);
        } catch (error) {
            console.error('Error parsing coordinates:', error);
            return [];
        }
    }

    // Verificar que sea array
    if (!Array.isArray(coordinates)) {
        console.error(
            'Coordinates is not an array after parsing:',
            coordinates,
        );
        return [];
    }

    console.log('Final parsed coordinates:', coordinates);
    console.log('Number of coordinate points:', coordinates.length);

    // Validar estructura de cada coordenada
    coordinates.forEach((coord, index) => {
        console.log(`Coordinate ${index}:`, coord);
        if (!coord.lat || !coord.lng) {
            console.error(`Invalid coordinate at index ${index}:`, coord);
        }
    });

    return coordinates;
});

// Función para cargar los datos del terreno del usuario en el mapa
const loadUserLandArea = () => {
    console.log('=== LOADING USER LAND AREA ===');
    console.log('User prop:', props.user);
    console.log(
        'User land_area_coordinates:',
        props.user.land_area_coordinates,
    );
    console.log('User land_area_size:', props.user.land_area_size);
    console.log('User land_area_name:', props.user.land_area_name);

    const coordinates = parsedCoordinates.value;
    console.log('Processed coordinates for LandAreaSelector:', coordinates);

    if (coordinates.length === 0) {
        console.log('No coordinates to display');
    } else {
        console.log(
            `Will display ${coordinates.length} coordinate points on map`,
        );
    }
};

onMounted(() => {
    loadUserLandArea();
});
</script>

<template>
    <Head title="Mi Perfil - ChakraSpace" />

    <div
        class="min-h-screen bg-gradient-to-br from-green-50 via-blue-50 to-amber-50"
    >
        <!-- Header -->
        <header
            class="border-b border-green-200 bg-white/80 shadow-sm backdrop-blur-sm"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <!-- Botón de regreso -->
                    <Link
                        href="/dashboard"
                        class="font-fredoka flex items-center space-x-2 rounded-lg bg-green-100 px-4 py-2 font-semibold text-green-800 transition-colors hover:bg-green-200"
                    >
                        <ArrowLeft class="h-5 w-5" />
                        <span>Volver al Dashboard</span>
                    </Link>

                    <!-- Título -->
                    <h1 class="font-fredoka text-2xl font-bold text-green-800">
                        Mi Perfil de Jugador
                    </h1>

                    <div></div>
                    <!-- Spacer -->
                </div>
            </div>
        </header>

        <!-- Contenido Principal -->
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-3">
                <!-- Información del Usuario -->
                <div class="lg:col-span-1">
                    <div class="rounded-2xl bg-white p-6 shadow-xl">
                        <div class="text-center">
                            <!-- Avatar -->
                            <div
                                class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg"
                            >
                                <User class="h-12 w-12 text-white" />
                            </div>

                            <!-- Nombre del Usuario -->
                            <h2
                                class="font-fredoka mb-2 text-2xl font-bold text-gray-800"
                            >
                                {{ user.name }}
                            </h2>
                            <p class="font-fredoka text-gray-600">
                                {{ user.email }}
                            </p>

                            <!-- Información de la Granja -->
                            <div
                                v-if="user.land_area_name"
                                class="mt-4 rounded-lg bg-green-50 p-4"
                            >
                                <div
                                    class="mb-2 flex items-center justify-center space-x-2"
                                >
                                    <MapPin class="h-5 w-5 text-green-600" />
                                    <h3
                                        class="font-fredoka font-semibold text-green-800"
                                    >
                                        {{ user.land_area_name }}
                                    </h3>
                                </div>
                                <p
                                    v-if="user.land_area_description"
                                    class="font-fredoka mb-2 text-sm text-green-700"
                                >
                                    {{ user.land_area_description }}
                                </p>
                                <p
                                    v-if="user.land_area_size"
                                    class="font-fredoka text-sm text-green-600"
                                >
                                    Área: {{ user.land_area_size }} hectáreas
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Mapa del Terreno -->
                    <div class="mt-6 rounded-2xl bg-white p-6 shadow-xl">
                        <div class="mb-4 flex items-center space-x-3">
                            <MapPin class="h-8 w-8 text-green-500" />
                            <h3
                                class="font-fredoka text-2xl font-bold text-gray-800"
                            >
                                Mi Terreno
                            </h3>
                        </div>

                        <div class="h-96 overflow-hidden rounded-lg">
                            <LandAreaSelector
                                ref="landAreaSelectorRef"
                                :readonly="true"
                                :show-controls="false"
                                :initial-coordinates="parsedCoordinates"
                                :initial-size="user.land_area_size || 0"
                                :initial-name="user.land_area_name || ''"
                                :initial-description="
                                    user.land_area_description || ''
                                "
                                class="h-full w-full"
                            />
                        </div>

                        <div
                            v-if="user.land_area_name"
                            class="mt-4 text-center"
                        >
                            <p class="font-fredoka text-sm text-gray-600">
                                Vista de tu área de cultivo registrada
                            </p>
                        </div>
                        <div v-else class="mt-4 text-center">
                            <p class="font-fredoka text-sm text-gray-500">
                                No tienes un área de cultivo registrada
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Progreso del Jugador -->
                <div class="lg:col-span-2">
                    <div class="space-y-6">
                        <!-- Nivel Actual -->
                        <div class="rounded-2xl bg-white p-6 shadow-xl">
                            <div class="mb-4 flex items-center space-x-3">
                                <Trophy class="h-8 w-8 text-yellow-500" />
                                <h3
                                    class="font-fredoka text-2xl font-bold text-gray-800"
                                >
                                    Nivel Actual
                                </h3>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div
                                    class="rounded-lg bg-gradient-to-br from-yellow-100 to-orange-100 p-4"
                                >
                                    <div class="text-center">
                                        <div
                                            class="font-fredoka text-3xl font-bold text-yellow-800"
                                        >
                                            {{ progreso.nivel_actual }}
                                        </div>
                                        <div
                                            class="font-fredoka text-yellow-700"
                                        >
                                            {{
                                                nivel_actual?.nombre ||
                                                'Principiante'
                                            }}
                                        </div>
                                    </div>
                                </div>

                                <div
                                    class="rounded-lg bg-gradient-to-br from-blue-100 to-indigo-100 p-4"
                                >
                                    <div class="text-center">
                                        <div
                                            class="font-fredoka text-2xl font-bold text-blue-800"
                                        >
                                            {{ progreso.xp_actual }} XP
                                        </div>
                                        <div class="font-fredoka text-blue-700">
                                            Experiencia Total
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="nivel_actual" class="mt-4">
                                <p
                                    class="font-fredoka text-center text-gray-700"
                                >
                                    {{ nivel_actual.descripcion }}
                                </p>
                            </div>
                        </div>

                        <!-- Progreso hacia el Siguiente Nivel -->
                        <div
                            v-if="siguiente_nivel"
                            class="rounded-2xl bg-white p-6 shadow-xl"
                        >
                            <div class="mb-4 flex items-center space-x-3">
                                <Target class="h-8 w-8 text-purple-500" />
                                <h3
                                    class="font-fredoka text-2xl font-bold text-gray-800"
                                >
                                    Progreso hacia {{ siguiente_nivel.nombre }}
                                </h3>
                            </div>

                            <!-- Barra de Progreso -->
                            <div class="mb-4">
                                <div
                                    class="mb-2 flex items-center justify-between"
                                >
                                    <span
                                        class="font-fredoka text-sm font-medium text-gray-700"
                                    >
                                        {{ progreso.xp_actual }} /
                                        {{
                                            siguiente_nivel.experiencia_requerida
                                        }}
                                        XP
                                    </span>
                                    <span
                                        class="font-fredoka text-sm font-medium text-purple-600"
                                    >
                                        {{
                                            Math.round(
                                                progreso.progreso_siguiente_nivel,
                                            )
                                        }}%
                                    </span>
                                </div>
                                <div
                                    class="h-4 w-full rounded-full bg-gray-200"
                                >
                                    <div
                                        class="h-4 rounded-full bg-gradient-to-r from-purple-500 to-indigo-600 transition-all duration-300"
                                        :style="{
                                            width: `${progreso.progreso_siguiente_nivel}%`,
                                        }"
                                    ></div>
                                </div>
                            </div>

                            <div class="text-center">
                                <p class="font-fredoka text-gray-700">
                                    {{ siguiente_nivel.descripcion }}
                                </p>
                                <p
                                    class="font-fredoka mt-2 text-sm text-gray-600"
                                >
                                    Necesitas
                                    {{
                                        siguiente_nivel.experiencia_requerida -
                                        progreso.xp_actual
                                    }}
                                    XP más
                                </p>
                            </div>

                            <!-- Indicador de Subida de Nivel -->
                            <div
                                v-if="progreso.puede_subir_nivel"
                                class="mt-4 rounded-lg border border-green-300 bg-green-100 p-4"
                            >
                                <div class="flex items-center space-x-2">
                                    <Zap class="h-5 w-5 text-green-600" />
                                    <span
                                        class="font-fredoka font-semibold text-green-800"
                                    >
                                        ¡Puedes subir de nivel!
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Nivel Máximo Alcanzado -->
                        <div v-else class="rounded-2xl bg-white p-6 shadow-xl">
                            <div class="text-center">
                                <Star
                                    class="mx-auto mb-4 h-16 w-16 text-yellow-500"
                                />
                                <h3
                                    class="font-fredoka mb-2 text-2xl font-bold text-gray-800"
                                >
                                    ¡Nivel Máximo Alcanzado!
                                </h3>
                                <p class="font-fredoka text-gray-700">
                                    Has alcanzado el nivel más alto disponible.
                                    ¡Felicidades!
                                </p>
                            </div>
                        </div>

                        <!-- Desbloqueos (si hay) -->
                        <div
                            v-if="desbloqueos && desbloqueos.length > 0"
                            class="rounded-2xl bg-white p-6 shadow-xl"
                        >
                            <h3
                                class="font-fredoka mb-4 text-2xl font-bold text-gray-800"
                            >
                                Desbloqueos Disponibles
                            </h3>
                            <div class="grid gap-3 md:grid-cols-2">
                                <div
                                    v-for="desbloqueo in desbloqueos"
                                    :key="desbloqueo.id"
                                    class="rounded-lg bg-gradient-to-r from-emerald-100 to-teal-100 p-3"
                                >
                                    <div
                                        class="font-fredoka font-semibold text-emerald-800"
                                    >
                                        {{ desbloqueo.nombre || 'Desbloqueo' }}
                                    </div>
                                    <div
                                        class="font-fredoka text-sm text-emerald-700"
                                    >
                                        {{
                                            desbloqueo.descripcion ||
                                            'Descripción no disponible'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
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
