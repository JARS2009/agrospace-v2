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
const plowMode = ref(false); // Estado para el modo de arado
const signMode = ref(false); // Estado para el modo de colocación de letreros
const potatoMode = ref(false); // Estado para el modo de siembra de papas
const alfalfaMode = ref(false); // Estado para el modo de siembra de alfalfa
const rainMode = ref(false); // Estado para el modo de lluvia
const sprinklerMode = ref(false); // Estado para el modo de aspersores
const harvestMode = ref(false); // Estado para el modo de cosecha
const showSignModal = ref(false); // Estado para mostrar el modal de texto
const signText = ref(''); // Texto del letrero
const showHarvestModal = ref(false); // Estado para mostrar el modal de cosecha
const harvestData = ref({ count: 0, quality: '', xp: 0 }); // Datos de la cosecha

// Datos de las tarjetas informativas
const infoCards = ref([
    {
        id: 1,
        title: 'Precipitaciones 2025',
        icon: '🌧️',
        summary: 'Patrón estacional claro',
        details:
            'Es el proceso en el que el agua cae de las nubes a la superficie terrestre en forma líquida o sólida, como lluvia, nieve o granizo. Enero: 86.1mm, Febrero: 101.3mm, Marzo: 87.4mm, Abril: 49.8mm, Mayo: 15.5mm, Junio: 127.7mm, Julio: 1.4mm, Agosto: 3.7mm, Septiembre: 0mm. Las precipitaciones son la principal fuente natural de agua para cultivos, ríos y reservorios. En Cajamarca, durante 2025, se observa un patrón estacional claro: Temporada de lluvias (enero a abril) con máximos en febrero y marzo, y temporada seca (mayo a septiembre) con lluvias casi nulas.',
    },
    {
        id: 2,
        title: 'Tipo de Suelo en Cajamarca',
        icon: '🏔️',
        summary: 'Franco Arenoso',
        details:
            'Este suelo retiene los nutrientes y tiene una textura que permite que las raíces de las plantas accedan a él, pero drena bien. Esto significa que, con el tiempo, el agua se filtra, evitando que las plantas se pudran, mientras que tiene una mezcla equilibrada de limo y arcilla. Es ideal para el cultivo ya que proporciona un balance perfecto entre retención de nutrientes y drenaje.',
    },
    {
        id: 3,
        title: 'Temperatura en Cajamarca 2025',
        icon: '🌡️',
        summary: 'Clima templado 21.5°C - 24.1°C',
        details:
            'Enero: 22.89°C, Febrero: 21.83°C, Marzo: 21.56°C, Abril: 22.1°C, Mayo: 22.43°C, Junio: 21.55°C, Julio: 22.99°C, Agosto: 24.15°C. En Cajamarca, la temperatura durante el año 2025 está entre 21.5°C y 24.1°C. Esto significa que no hace ni mucho frío ni mucho calor, sino un clima templado y agradable. Gracias a ese clima, el agua que se usa para regar no se evapora muy rápido, pero las plantas sí tienen suficiente calor para crecer bien. Hay una evapotranspiración moderada.',
    },
    {
        id: 4,
        title: 'Sistemas de Riego',
        icon: '💧',
        summary: 'Necesarios en temporada seca',
        details:
            'Cuando termina la temporada de lluvias (mayo-septiembre), los agricultores necesitan usar sistemas de riego para que las plantas sigan creciendo y no se sequen. El agua de lluvia no alcanza todo el año para los cultivos, por lo que es fundamental contar con sistemas de irrigación eficientes durante los meses secos.',
    },
]);

// Función para seleccionar tarjeta
const selectCard = (cardId: number) => {
    selectedCard.value = selectedCard.value === cardId ? null : cardId;
};

// Función para alternar el modo de arado
const togglePlowMode = () => {
    plowMode.value = !plowMode.value;
    // Desactivar modo letrero, papa, alfalfa, lluvia, aspersores y cosecha si se activa arado
    if (plowMode.value) {
        signMode.value = false;
        potatoMode.value = false;
        alfalfaMode.value = false;
        rainMode.value = false;
        sprinklerMode.value = false;
        harvestMode.value = false;
    }
};

// Función para alternar el modo de siembra de papas
const togglePotatoMode = () => {
    potatoMode.value = !potatoMode.value;
    // Desactivar otros modos si se activa papa
    if (potatoMode.value) {
        plowMode.value = false;
        signMode.value = false;
        alfalfaMode.value = false;
        rainMode.value = false;
        sprinklerMode.value = false;
        harvestMode.value = false;
    }
};

// Función para alternar el modo de siembra de alfalfa
const toggleAlfalfaMode = () => {
    alfalfaMode.value = !alfalfaMode.value;
    // Desactivar otros modos si se activa alfalfa
    if (alfalfaMode.value) {
        plowMode.value = false;
        signMode.value = false;
        potatoMode.value = false;
        rainMode.value = false;
        sprinklerMode.value = false;
        harvestMode.value = false;
    }
};

// Función para alternar el modo de lluvia
const toggleRainMode = () => {
    rainMode.value = !rainMode.value;
    // Desactivar otros modos si se activa lluvia
    if (rainMode.value) {
        plowMode.value = false;
        signMode.value = false;
        potatoMode.value = false;
        alfalfaMode.value = false;
        sprinklerMode.value = false;
        harvestMode.value = false;
    }
};

// Función para alternar el modo de aspersores
const toggleSprinklerMode = () => {
    sprinklerMode.value = !sprinklerMode.value;
    // Desactivar otros modos si se activa aspersores
    if (sprinklerMode.value) {
        plowMode.value = false;
        signMode.value = false;
        potatoMode.value = false;
        alfalfaMode.value = false;
        rainMode.value = false;
        harvestMode.value = false;
    }
};

// Función para alternar el modo de cosecha
const toggleHarvestMode = () => {
    harvestMode.value = !harvestMode.value;
    // Desactivar otros modos si se activa cosecha
    if (harvestMode.value) {
        plowMode.value = false;
        signMode.value = false;
        potatoMode.value = false;
        alfalfaMode.value = false;
        rainMode.value = false;
        sprinklerMode.value = false;
    }
};

// Función para activar el modo de colocación de letreros
const activateSignMode = () => {
    if (signText.value.trim()) {
        signMode.value = true;
        plowMode.value = false; // Desactivar modo arado
        potatoMode.value = false; // Desactivar modo papa
        alfalfaMode.value = false; // Desactivar modo alfalfa
        rainMode.value = false; // Desactivar modo lluvia
        sprinklerMode.value = false; // Desactivar modo aspersores
        harvestMode.value = false; // Desactivar modo cosecha
        showSignModal.value = false;
    }
};

// Función para abrir el modal de texto del letrero
const openSignModal = () => {
    showSignModal.value = true;
    signText.value = '';
};

// Función para cancelar la colocación de letrero
const cancelSignMode = () => {
    signMode.value = false;
    signText.value = '';
};

// Función para manejar la colocación exitosa del letrero
const onSignPlaced = () => {
    signMode.value = false;
    signText.value = '';
};

// Función para manejar la cosecha de papas
const onPotatoHarvested = (count: number, quality: string, xp: number) => {
    harvestData.value = { count, quality, xp };
    showHarvestModal.value = true;
    harvestMode.value = false; // Desactivar modo cosecha después de cosechar
};

// Función para cerrar el modal de cosecha
const closeHarvestModal = () => {
    showHarvestModal.value = false;
};
</script>

<template>
    <Head title="Dashboard - ChakraSpace" />

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
                            ChakraSpace
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
                                            siguiente_nivel?.experiencia_requerida ||
                                            progreso.xp_actual
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
                        <FarmScene3D
                            :plowMode="plowMode"
                            :signMode="signMode"
                            :potatoMode="potatoMode"
                            :alfalfaMode="alfalfaMode"
                            :rainMode="rainMode"
                            :sprinklerMode="sprinklerMode"
                            :harvestMode="harvestMode"
                            :signText="signText"
                            @signPlaced="onSignPlaced"
                            @potatoHarvested="onPotatoHarvested"
                        />
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

                        <div class="space-y-2 max-h-[calc(100vh-20rem)] overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-green-300 scrollbar-track-green-100">
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
                        <!-- Maíz - Bloqueado -->
                        <button
                            class="font-fredoka relative flex h-15 w-16 flex-shrink-0 cursor-not-allowed flex-col items-center justify-center rounded-lg border-2 border-gray-300 bg-gray-200 opacity-60 transition-all"
                            disabled
                        >
                            <div class="text-lg">🌽</div>
                            <span class="text-xs font-medium text-gray-600"
                                >Maíz</span
                            >
                            <!-- Candado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
                            >
                                <span class="text-xs text-white">🔒</span>
                            </div>
                            <!-- Indicador de nivel -->
                            <div
                                class="absolute right-0 -bottom-1 left-0 rounded-b-lg bg-red-500 px-1 text-xs text-white"
                            >
                                Nivel 3
                            </div>
                        </button>

                        <!-- Tomate - Bloqueado -->
                        <button
                            class="font-fredoka relative flex h-15 w-16 flex-shrink-0 cursor-not-allowed flex-col items-center justify-center rounded-lg border-2 border-gray-300 bg-gray-200 opacity-60 transition-all"
                            disabled
                        >
                            <div class="text-lg">🍅</div>
                            <span class="text-xs font-medium text-gray-600"
                                >Tomate</span
                            >
                            <!-- Candado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
                            >
                                <span class="text-xs text-white">🔒</span>
                            </div>
                            <!-- Indicador de nivel -->
                            <div
                                class="absolute right-0 -bottom-1 left-0 rounded-b-lg bg-red-500 px-1 text-xs text-white"
                            >
                                Nivel 2
                            </div>
                        </button>

                        <!-- Papa - Desbloqueada -->
                        <button
                            @click="togglePotatoMode"
                            class="font-fredoka relative flex h-15 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 transition-all hover:scale-105"
                            :class="
                                potatoMode
                                    ? 'border-amber-400 bg-amber-300 text-amber-900 shadow-lg'
                                    : 'border-amber-200 bg-amber-100 hover:border-amber-300 hover:bg-amber-200'
                            "
                        >
                            <div class="text-lg">🥔</div>
                            <span
                                class="text-xs font-medium"
                                :class="
                                    potatoMode
                                        ? 'text-amber-900'
                                        : 'text-amber-800'
                                "
                                >Papa</span
                            >
                            <!-- Indicador de desbloqueado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500"
                            >
                                <span class="text-xs text-white">✓</span>
                            </div>
                        </button>

                        <!-- Alfalfa - Desbloqueada -->
                        <button
                            @click="toggleAlfalfaMode"
                            class="font-fredoka relative flex h-15 w-16 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 transition-all hover:scale-105"
                            :class="
                                alfalfaMode
                                    ? 'border-green-400 bg-green-300 text-green-900 shadow-lg'
                                    : 'border-green-200 bg-green-100 hover:border-green-300 hover:bg-green-200'
                            "
                        >
                            <div class="text-lg">🍀</div>
                            <span
                                class="text-xs font-medium"
                                :class="
                                    alfalfaMode
                                        ? 'text-green-900'
                                        : 'text-green-800'
                                "
                                >Alfalfa</span
                            >
                            <!-- Indicador de desbloqueado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500"
                            >
                                <span class="text-xs text-white">✓</span>
                            </div>
                        </button>

                        <!-- Lechuga - Bloqueada -->
                        <button
                            class="font-fredoka relative flex h-15 w-16 flex-shrink-0 cursor-not-allowed flex-col items-center justify-center rounded-lg border-2 border-gray-300 bg-gray-200 opacity-60 transition-all"
                            disabled
                        >
                            <div class="text-lg">🥬</div>
                            <span class="text-xs font-medium text-gray-600"
                                >Lechuga</span
                            >
                            <!-- Candado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
                            >
                                <span class="text-xs text-white">🔒</span>
                            </div>
                            <!-- Indicador de nivel -->
                            <div
                                class="absolute right-0 -bottom-1 left-0 rounded-b-lg bg-red-500 px-1 text-xs text-white"
                            >
                                Nivel 4
                            </div>
                        </button>

                        <!-- Zanahoria - Bloqueada -->
                        <button
                            class="font-fredoka relative flex h-15 w-16 flex-shrink-0 cursor-not-allowed flex-col items-center justify-center rounded-lg border-2 border-gray-300 bg-gray-200 opacity-60 transition-all"
                            disabled
                        >
                            <div class="text-lg">🥕</div>
                            <span class="text-xs font-medium text-gray-600"
                                >Zanahoria</span
                            >
                            <!-- Candado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
                            >
                                <span class="text-xs text-white">🔒</span>
                            </div>
                            <!-- Indicador de nivel -->
                            <div
                                class="absolute right-0 -bottom-1 left-0 rounded-b-lg bg-red-500 px-1 text-xs text-white"
                            >
                                Nivel 5
                            </div>
                        </button>

                        <!-- Girasol - Bloqueado -->
                        <button
                            class="font-fredoka relative flex h-15 w-16 flex-shrink-0 cursor-not-allowed flex-col items-center justify-center rounded-lg border-2 border-gray-300 bg-gray-200 opacity-60 transition-all"
                            disabled
                        >
                            <div class="text-lg">🌻</div>
                            <span class="text-xs font-medium text-gray-600"
                                >Girasol</span
                            >
                            <!-- Candado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
                            >
                                <span class="text-xs text-white">🔒</span>
                            </div>
                            <!-- Indicador de nivel -->
                            <div
                                class="absolute right-0 -bottom-1 left-0 rounded-b-lg bg-red-500 px-1 text-xs text-white"
                            >
                                Nivel 6
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Sección de Tipos de Riego -->
                <div
                    class="rounded-xl bg-white/90 p-2 shadow-md backdrop-blur-sm lg:col-span-3"
                >
                    <h3
                        class="font-fredoka mb-2 flex items-center text-sm font-bold text-blue-800"
                    >
                        <span class="mr-2">💧</span>
                        Tipos de Riego
                    </h3>

                    <div class="flex gap-2 overflow-x-auto px-1 py-1 pb-2">
                        <!-- Aspersión - Disponible -->
                        <button
                            @click="toggleSprinklerMode"
                            class="font-fredoka relative flex h-15 w-18 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 transition-all hover:scale-105"
                            :class="
                                sprinklerMode
                                    ? 'border-cyan-400 bg-cyan-300 text-cyan-900 shadow-lg'
                                    : 'border-blue-200 bg-blue-100 hover:border-blue-300 hover:bg-blue-200'
                            "
                        >
                            <div class="text-lg">🚿</div>
                            <span
                                class="text-center text-xs font-medium"
                                :class="
                                    sprinklerMode
                                        ? 'text-cyan-900'
                                        : 'text-blue-800'
                                "
                                >Aspersión</span
                            >
                            <!-- Indicador de disponible -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-green-500"
                            >
                                <span class="text-xs text-white">✓</span>
                            </div>
                        </button>

                        <!-- Goteo - Bloqueado -->
                        <button
                            class="font-fredoka relative flex h-15 w-18 flex-shrink-0 cursor-not-allowed flex-col items-center justify-center rounded-lg border-2 border-gray-300 bg-gray-200 opacity-60 transition-all"
                            disabled
                        >
                            <div class="text-lg">💧</div>
                            <span
                                class="text-center text-xs font-medium text-gray-600"
                                >Goteo</span
                            >
                            <!-- Candado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
                            >
                                <span class="text-xs text-white">🔒</span>
                            </div>
                            <!-- Indicador de nivel -->
                            <div
                                class="absolute right-0 -bottom-1 left-0 rounded-b-lg bg-red-500 px-1 text-xs text-white"
                            >
                                Nivel 3
                            </div>
                        </button>

                        <!-- Inundación - Bloqueado -->
                        <button
                            class="font-fredoka relative flex h-15 w-18 flex-shrink-0 cursor-not-allowed flex-col items-center justify-center rounded-lg border-2 border-gray-300 bg-gray-200 opacity-60 transition-all"
                            disabled
                        >
                            <div class="text-lg">🌊</div>
                            <span
                                class="text-center text-xs font-medium text-gray-600"
                                >Inundación</span
                            >
                            <!-- Candado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
                            >
                                <span class="text-xs text-white">🔒</span>
                            </div>
                            <!-- Indicador de nivel -->
                            <div
                                class="absolute right-0 -bottom-1 left-0 rounded-b-lg bg-red-500 px-1 text-xs text-white"
                            >
                                Nivel 5
                            </div>
                        </button>

                        <!-- Automático - Bloqueado -->
                        <button
                            class="font-fredoka relative flex h-15 w-18 flex-shrink-0 cursor-not-allowed flex-col items-center justify-center rounded-lg border-2 border-gray-300 bg-gray-200 opacity-60 transition-all"
                            disabled
                        >
                            <div class="text-lg">⚡</div>
                            <span
                                class="text-center text-xs font-medium text-gray-600"
                                >Automático</span
                            >
                            <!-- Candado -->
                            <div
                                class="absolute -top-1 -right-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500"
                            >
                                <span class="text-xs text-white">🔒</span>
                            </div>
                            <!-- Indicador de nivel -->
                            <div
                                class="absolute right-0 -bottom-1 left-0 rounded-b-lg bg-red-500 px-1 text-xs text-white"
                            >
                                Nivel 8
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Sección de Acciones Especiales -->
                <div
                    class="rounded-xl bg-white/90 p-2 shadow-md backdrop-blur-sm lg:col-span-4"
                >
                    <h3
                        class="font-fredoka mb-2 flex items-center text-sm font-bold text-amber-800"
                    >
                        <span class="mr-2">⚙️</span>
                        Acciones Especiales
                    </h3>

                    <div class="flex gap-2 overflow-x-auto px-1 py-1 pb-2">
                        <button
                            @click="togglePlowMode"
                            class="font-fredoka flex h-15 w-24 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 transition-all hover:scale-105"
                            :class="
                                plowMode
                                    ? 'border-amber-400 bg-amber-300 text-amber-900 shadow-lg'
                                    : 'border-amber-200 bg-amber-100 hover:border-amber-300 hover:bg-amber-200'
                            "
                        >
                            <div class="text-lg">🚜</div>
                            <span
                                class="text-center text-xs font-medium"
                                :class="
                                    plowMode
                                        ? 'text-amber-900'
                                        : 'text-amber-800'
                                "
                                >{{
                                    plowMode ? 'Desactivar' : 'Preparar Terreno'
                                }}</span
                            >
                        </button>

                        <button
                            @click="openSignModal"
                            class="font-fredoka flex h-15 w-24 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 transition-all hover:scale-105"
                            :class="
                                signMode
                                    ? 'border-brown-400 bg-brown-300 text-brown-900 shadow-lg'
                                    : 'border-yellow-600 bg-yellow-200 hover:border-yellow-700 hover:bg-yellow-300'
                            "
                        >
                            <div class="text-lg">🪧</div>
                            <span
                                class="text-center text-xs font-medium"
                                :class="
                                    signMode
                                        ? 'text-brown-900'
                                        : 'text-yellow-800'
                                "
                                >{{ signMode ? 'Colocando' : 'Letrero' }}</span
                            >
                        </button>

                        <button
                            @click="toggleRainMode"
                            class="font-fredoka flex h-15 w-24 flex-shrink-0 flex-col items-center justify-center rounded-lg border-2 transition-all hover:scale-105"
                            :class="
                                rainMode
                                    ? 'border-blue-400 bg-blue-300 text-blue-900 shadow-lg'
                                    : 'border-blue-200 bg-blue-100 hover:border-blue-300 hover:bg-blue-200'
                            "
                        >
                            <div class="text-lg">🌧️</div>
                            <span
                                class="text-center text-xs font-medium"
                                :class="
                                    rainMode ? 'text-blue-900' : 'text-blue-800'
                                "
                                >{{
                                    rainMode ? 'Lloviendo' : 'Riego Natural'
                                }}</span
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

        <!-- Modal para texto del letrero -->
        <div
            v-if="showSignModal"
            class="bg-opacity-50 fixed inset-0 z-50 flex items-center justify-center bg-black"
        >
            <div
                class="mx-4 w-full max-w-md rounded-xl bg-white p-6 shadow-2xl"
            >
                <h3 class="font-fredoka mb-4 text-xl font-bold text-gray-800">
                    🪧 Crear Letrero
                </h3>
                <p class="font-fredoka mb-4 text-gray-600">
                    Escribe el texto que quieres mostrar en el letrero:
                </p>
                <input
                    v-model="signText"
                    type="text"
                    placeholder="Ej: Bienvenidos a mi granja"
                    class="font-fredoka w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-200 focus:outline-none"
                    maxlength="50"
                    @keyup.enter="activateSignMode"
                />
                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        @click="showSignModal = false"
                        class="font-fredoka px-4 py-2 text-gray-600 transition-colors hover:text-gray-800"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="activateSignMode"
                        :disabled="!signText.trim()"
                        class="font-fredoka rounded-lg bg-yellow-500 px-6 py-2 text-white transition-colors hover:bg-yellow-600 disabled:cursor-not-allowed disabled:bg-gray-300"
                    >
                        Colocar Letrero
                    </button>
                </div>
            </div>
        </div>

        <!-- Indicador de modo letrero activo -->
        <div
            v-if="signMode"
            class="fixed top-4 left-1/2 z-40 -translate-x-1/2 transform rounded-lg bg-yellow-500 px-6 py-3 text-white shadow-lg"
        >
            <div class="font-fredoka flex items-center space-x-2 font-semibold">
                <span>🪧</span>
                <span>Modo Letrero Activo</span>
                <button
                    @click="cancelSignMode"
                    class="ml-4 text-yellow-200 hover:text-white"
                >
                    ✕
                </button>
            </div>
            <p class="font-fredoka mt-1 text-sm opacity-90">
                Haz clic en el terreno para colocar: "{{ signText }}"
            </p>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap');

.font-fredoka {
    font-family: 'Fredoka', sans-serif;
}
</style>
