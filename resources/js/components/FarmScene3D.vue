<template>
    <div class="relative h-full w-full overflow-hidden rounded-xl">
        <!-- Pantalla de Inicio/Pausa -->
        <div
            v-if="!gameStarted"
            class="absolute inset-0 z-30 flex items-center justify-center bg-gradient-to-br from-green-900 via-green-800 to-emerald-900"
        >
            <div class="text-center text-white">
                <!-- Logo/Icono del juego -->
                <div class="mb-8">
                    <div
                        class="mx-auto mb-4 flex h-24 w-24 items-center justify-center rounded-full bg-green-600 shadow-2xl"
                    >
                        <span class="text-4xl">🚜</span>
                    </div>
                    <h1 class="font-fredoka mb-2 text-4xl font-bold text-white">
                        ChakraSpace 3D
                    </h1>
                    <p class="text-lg opacity-80">
                        Simulador de Granja Interactivo
                    </p>
                </div>

                <!-- Descripción del juego -->
                <div class="mx-auto mb-8 max-w-md">
                    <p class="text-base leading-relaxed opacity-90">
                        Explora tu granja en 3D, ara la tierra, siembra
                        cultivos, controla el clima y gestiona tu sistema de
                        riego.
                    </p>
                </div>

                <!-- Botón de Inicio -->
                <button
                    @click="startGame"
                    class="font-fredoka group hover:shadow-3xl relative overflow-hidden rounded-xl bg-gradient-to-r from-green-500 to-emerald-500 px-8 py-4 text-xl font-bold text-white shadow-2xl transition-all duration-300 hover:scale-105 hover:from-green-400 hover:to-emerald-400"
                >
                    <span class="relative z-10 flex items-center space-x-3">
                        <span>🌱</span>
                        <span>Iniciar Granja</span>
                        <span>🌾</span>
                    </span>

                    <!-- Efecto de brillo -->
                    <div
                        class="absolute inset-0 -translate-x-full -skew-x-12 transform bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-all duration-700 group-hover:translate-x-full group-hover:opacity-20"
                    ></div>
                </button>

                <!-- Indicadores de características -->
                <div
                    class="mt-8 flex justify-center space-x-6 text-sm opacity-75"
                >
                    <div class="flex items-center space-x-1">
                        <span>🚜</span>
                        <span>Arado</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span>🌱</span>
                        <span>Siembra</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span>🌧️</span>
                        <span>Clima</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <span>💧</span>
                        <span>Riego</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading Screen -->
        <div
            v-if="isLoading && gameStarted"
            class="absolute inset-0 z-10 flex items-center justify-center bg-gradient-to-br from-green-900 to-green-700"
        >
            <div class="text-center text-white">
                <div class="mb-4">
                    <div
                        class="mx-auto h-16 w-16 animate-spin rounded-full border-b-2 border-white"
                    ></div>
                </div>
                <h2 class="mb-2 text-2xl font-bold">Cargando Escena 3D</h2>
                <p class="text-lg opacity-80">{{ loadingMessage }}</p>
                <div class="mx-auto mt-4 h-2 w-64 rounded-full bg-green-800">
                    <div
                        class="h-2 rounded-full bg-white transition-all duration-300"
                        :style="{ width: loadingProgress + '%' }"
                    ></div>
                </div>
                <p class="mt-2 text-sm opacity-60">
                    {{ Math.round(loadingProgress) }}%
                </p>
            </div>
        </div>

        <!-- 3D Scene Container -->
        <div
            ref="sceneContainer"
            class="h-full w-full"
            :class="{
                'cursor-tractor': plowMode,
                'cursor-sign': signMode,
                'cursor-potato': potatoMode,
                'cursor-alfalfa': alfalfaMode,
                'cursor-rain': rainMode,
                'cursor-sprinkler': sprinklerMode,
                'cursor-harvest': harvestMode,
            }"
        ></div>

        <!-- Modo de Arado Indicator -->
        <div
            v-if="plowMode"
            class="font-fredoka absolute top-4 left-4 z-20 rounded-lg bg-amber-500 px-4 py-2 font-semibold text-white shadow-lg"
        >
            🚜 Modo Arado Activo - Haz clic para arar
        </div>

        <!-- Modo de Letrero Indicator -->
        <div
            v-if="signMode"
            class="font-fredoka absolute top-4 left-4 z-20 rounded-lg bg-yellow-500 px-4 py-2 font-semibold text-white shadow-lg"
        >
            🪧 Modo Letrero Activo - Haz clic para colocar
        </div>

        <!-- Modo de Papa Indicator -->
        <div
            v-if="potatoMode"
            class="font-fredoka absolute top-4 left-4 z-20 rounded-lg bg-amber-600 px-4 py-2 font-semibold text-white shadow-lg"
        >
            🥔 Modo Papa Activo - Haz clic para sembrar
        </div>

        <!-- Modo de Alfalfa Indicator -->
        <div
            v-if="alfalfaMode"
            class="font-fredoka absolute top-4 left-4 z-20 rounded-lg bg-green-600 px-4 py-2 font-semibold text-white shadow-lg"
        >
            🍀 Modo Alfalfa Activo - Haz clic para sembrar
        </div>

        <!-- Modo de Lluvia Indicator -->
        <div
            v-if="rainMode"
            class="font-fredoka absolute top-4 left-4 z-20 rounded-lg bg-blue-600 px-4 py-2 font-semibold text-white shadow-lg"
        >
            🌧️ Lluvia Activa - Haz clic para hacer llover
        </div>

        <!-- Modo de Aspersores Indicator -->
        <div
            v-if="sprinklerMode"
            class="font-fredoka absolute top-4 left-4 z-20 rounded-lg bg-cyan-600 px-4 py-2 font-semibold text-white shadow-lg"
        >
            💧 Modo Aspersores - Haz clic para colocar
        </div>

        <!-- Modo de Cosecha Indicator -->
        <div
            v-if="harvestMode"
            class="font-fredoka absolute top-4 left-4 z-20 rounded-lg bg-orange-600 px-4 py-2 font-semibold text-white shadow-lg"
        >
            🌾 Modo Cosecha - Haz clic en las plantas para cosechar
        </div>

        <!-- Control de Riego por Aspersión -->
        <div
            v-if="placedSprinklers.length > 0"
            class="font-fredoka absolute top-4 right-4 z-20 flex flex-col gap-2"
        >
            <button
                @click="toggleSprinklerSystem"
                :class="[
                    'rounded-lg px-4 py-2 font-semibold text-white shadow-lg transition-all duration-300',
                    sprinklerSystemActive
                        ? 'bg-red-500 hover:bg-red-600'
                        : 'bg-green-500 hover:bg-green-600',
                ]"
            >
                {{
                    sprinklerSystemActive
                        ? '🛑 Detener Riego'
                        : '💧 Activar Riego'
                }}
            </button>
            <div
                class="bg-opacity-50 rounded bg-black px-2 py-1 text-sm text-white"
            >
                Aspersores: {{ placedSprinklers.length }}
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { onMounted, onUnmounted, ref, watch } from 'vue';

// Props para comunicación con el componente padre
interface Props {
    plowMode?: boolean;
    signMode?: boolean;
    potatoMode?: boolean;
    alfalfaMode?: boolean;
    rainMode?: boolean;
    sprinklerMode?: boolean;
    harvestMode?: boolean;
    signText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    plowMode: false,
    signMode: false,
    potatoMode: false,
    alfalfaMode: false,
    rainMode: false,
    sprinklerMode: false,
    harvestMode: false,
    signText: '',
});

// Emits para comunicar cambios al componente padre
const emit = defineEmits<{
    plowModeChanged: [value: boolean];
    signPlaced: [];
    potatoHarvested: [count: number, quality: string, xp: number];
}>();

const sceneContainer = ref<HTMLElement>();
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let renderer: THREE.WebGLRenderer;
let controls: OrbitControls;
let animationId: number;

let gltfLoader: GLTFLoader;
let textureLoader: THREE.TextureLoader;

// Variables para el sistema de arado
let terrain: THREE.Mesh;
let raycaster: THREE.Raycaster;
let mouse: THREE.Vector2;
let plowedTiles = ref(new Set<string>());

// Variables para previsualización
let previewTile: THREE.Mesh | null = null;
let previewMaterial: THREE.MeshStandardMaterial;
let previewSignMaterial: THREE.MeshStandardMaterial;

// Variables para previsualización de letreros
let previewSign: THREE.Group | null = null;

// Texturas para tierra normal y arada
let normalTerrainMaterial: THREE.MeshStandardMaterial;
let plowedTerrainMaterial: THREE.MeshStandardMaterial;

// Grid de parcelas aradas
const tileSize = 3; // Tamaño de cada parcela en unidades del mundo
const plowedAreas: Map<string, THREE.Mesh> = new Map();

// Array para almacenar los letreros colocados
const placedSigns: THREE.Group[] = [];

// Array para almacenar las papas plantadas
const plantedPotatoes: THREE.Group[] = [];

// Variables para previsualización de papas
let previewPotato: THREE.Group | null = null;
let potatoModel: THREE.Group | null = null;

// Array para almacenar las alfalfas plantadas
const plantedAlfalfas: THREE.Group[] = [];

// Variables para previsualización de alfalfa
let previewAlfalfa: THREE.Group | null = null;
let alfalfaModel: THREE.Group | null = null;

// Variables para el sistema de lluvia
let rainParticleSystem: THREE.Points | null = null;
let rainGeometry: THREE.BufferGeometry | null = null;
let rainMaterial: THREE.PointsMaterial | null = null;
let rainDrops: Float32Array | null = null;
let isRaining = ref(false);
let rainIntensity = ref(0);
let originalLightIntensity = 1;
let originalAmbientIntensity = 0.4;
let directionalLight: THREE.DirectionalLight | null = null;
let ambientLight: THREE.AmbientLight | null = null;

// Variables para efectos de agua en el suelo
let puddleGeometries: THREE.PlaneGeometry[] = [];
let puddleMaterials: THREE.MeshStandardMaterial[] = [];
let puddles: THREE.Mesh[] = [];

// Variables para el sistema de aspersores
const placedSprinklers: THREE.Group[] = [];
let previewSprinkler: THREE.Group | null = null;
let sprinklerModel: THREE.Group | null = null;
const sprinklerSystemActive = ref(false);
let sprinklerWaterEffects: THREE.Points[] = [];
let sprinklerAnimations: number[] = [];

// Audio variables for rain sounds
let rainAudio: HTMLAudioElement | null = null;
let thunderAudio: HTMLAudioElement | null = null;
const rainVolume = ref(0.3);
const soundsEnabled = ref(true);

// Loading state variables
const isLoading = ref(true);
const loadingMessage = ref('Inicializando...');
const loadingProgress = ref(0);
const gameStarted = ref(false); // Nueva variable para controlar si el juego ha iniciado
let totalAssets = 0;
let loadedAssets = 0;

// Loading progress functions
const updateLoadingProgress = () => {
    loadedAssets++;
    loadingProgress.value = (loadedAssets / totalAssets) * 100;

    if (loadedAssets >= totalAssets) {
        setTimeout(() => {
            isLoading.value = false;
        }, 500); // Small delay to show 100% completion
    }
};

// Función para cargar el modelo de aspersores
const loadSprinklerModel = async () => {
    setLoadingMessage('Cargando modelo de aspersores...');

    try {
        const gltf = await gltfLoader.loadAsync('/models/sprinkler/scene.gltf');
        sprinklerModel = gltf.scene.clone();

        // Escalar el modelo apropiadamente
        sprinklerModel.scale.set(0.3, 0.3, 0.3);

        // Habilitar sombras
        sprinklerModel.traverse((child) => {
            if (child instanceof THREE.Mesh) {
                child.castShadow = true;
                child.receiveShadow = true;
            }
        });

        updateLoadingProgress();
        console.log('Modelo de aspersores cargado exitosamente');
    } catch (error) {
        console.error('Error cargando modelo de aspersores:', error);
        updateLoadingProgress();
    }
};

// Función para cargar el modelo de alfalfa
const loadAlfalfaModel = async () => {
    setLoadingMessage('Cargando modelo de alfalfa...');

    try {
        const gltf = await gltfLoader.loadAsync(
            '/models/clover_grass/scene.gltf',
        );
        alfalfaModel = gltf.scene.clone();

        // Escalar el modelo para que sea muy pequeño
        alfalfaModel.scale.set(0.01, 0.01, 0.01);

        // Habilitar sombras
        alfalfaModel.traverse((child) => {
            if (child instanceof THREE.Mesh) {
                child.castShadow = true;
                child.receiveShadow = true;
            }
        });

        updateLoadingProgress();
        console.log('Modelo de alfalfa cargado exitosamente');
    } catch (error) {
        console.error('Error cargando modelo de alfalfa:', error);
        updateLoadingProgress();
    }
};

// Función para actualizar la previsualización de alfalfa
const updateAlfalfaPreview = (x: number, z: number) => {
    if (!props.alfalfaMode || !alfalfaModel) {
        if (previewAlfalfa) {
            scene.remove(previewAlfalfa);
            previewAlfalfa = null;
        }
        return;
    }

    // Verificar que estemos en terreno arado
    const tileKey = `${Math.floor(x / tileSize)}_${Math.floor(z / tileSize)}`;
    if (!plowedAreas.has(tileKey)) {
        if (previewAlfalfa) {
            scene.remove(previewAlfalfa);
            previewAlfalfa = null;
        }
        return;
    }

    // Verificar que no haya alfalfa muy cerca
    const minDistance = 1.5;
    const tooClose = plantedAlfalfas.some((alfalfa) => {
        const distance = Math.sqrt(
            Math.pow(alfalfa.position.x - x, 2) +
                Math.pow(alfalfa.position.z - z, 2),
        );
        return distance < minDistance;
    });

    if (tooClose) {
        if (previewAlfalfa) {
            scene.remove(previewAlfalfa);
            previewAlfalfa = null;
        }
        return;
    }

    if (previewAlfalfa) {
        previewAlfalfa.position.set(x, 0, z);
    } else {
        // Crear previsualización de alfalfa
        previewAlfalfa = alfalfaModel.clone();

        // Hacer la previsualización semi-transparente
        previewAlfalfa.traverse((child) => {
            if (child instanceof THREE.Mesh) {
                const material = child.material.clone();
                material.transparent = true;
                material.opacity = 0.5;
                child.material = material;
            }
        });

        previewAlfalfa.position.set(x, 0, z);
        scene.add(previewAlfalfa);
    }
};

const setLoadingMessage = (message: string) => {
    loadingMessage.value = message;
};

// Función para crear materiales de terreno diferenciados
const createTerrainMaterials = () => {
    setLoadingMessage('Cargando texturas del terreno...');

    // Material para tierra normal (Ground068 - más natural)
    const normalColorTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_Color.jpg',
        updateLoadingProgress,
    );
    const normalNormalTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_NormalGL.jpg',
        updateLoadingProgress,
    );
    const normalRoughnessTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_Roughness.jpg',
        updateLoadingProgress,
    );
    const normalAoTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_AmbientOcclusion.jpg',
        updateLoadingProgress,
    );
    const normalDisplacementTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_Displacement.jpg',
        updateLoadingProgress,
    );

    // Material para tierra arada/removida - usando textura personalizada rica en nutrientes
    const richSoilTexture = textureLoader.load(
        '/textures/rich_soil_texture.svg',
        updateLoadingProgress,
    );
    const plowedNormalTexture = textureLoader.load(
        '/textures/Ground048_1K-JPG_NormalGL.jpg',
        updateLoadingProgress,
    );
    const plowedRoughnessTexture = textureLoader.load(
        '/textures/Ground048_1K-JPG_Roughness.jpg',
        updateLoadingProgress,
    );
    const plowedAoTexture = textureLoader.load(
        '/textures/Ground048_1K-JPG_AmbientOcclusion.jpg',
        updateLoadingProgress,
    );
    const plowedDisplacementTexture = textureLoader.load(
        '/textures/Ground048_1K-JPG_Displacement.jpg',
        updateLoadingProgress,
    );

    // Configurar repetición de texturas para que se vea natural
    const normalTextureRepeat = 8;
    const plowedTextureRepeat = 4; // Menos repetición para tierra arada para más detalle

    [
        normalColorTexture,
        normalNormalTexture,
        normalRoughnessTexture,
        normalAoTexture,
        normalDisplacementTexture,
    ].forEach((texture) => {
        texture.wrapS = THREE.RepeatWrapping;
        texture.wrapT = THREE.RepeatWrapping;
        texture.repeat.set(normalTextureRepeat, normalTextureRepeat);
    });

    [
        richSoilTexture,
        plowedNormalTexture,
        plowedRoughnessTexture,
        plowedAoTexture,
        plowedDisplacementTexture,
    ].forEach((texture) => {
        texture.wrapS = THREE.RepeatWrapping;
        texture.wrapT = THREE.RepeatWrapping;
        texture.repeat.set(plowedTextureRepeat, plowedTextureRepeat);
    });

    // Crear material para tierra normal
    normalTerrainMaterial = new THREE.MeshStandardMaterial({
        map: normalColorTexture,
        normalMap: normalNormalTexture,
        roughnessMap: normalRoughnessTexture,
        aoMap: normalAoTexture,
        displacementMap: normalDisplacementTexture,
        displacementScale: 0.05,
        aoMapIntensity: 1.0,
    });

    // Crear material para tierra arada/removida con textura rica y color natural
    plowedTerrainMaterial = new THREE.MeshStandardMaterial({
        map: richSoilTexture,
        normalMap: plowedNormalTexture,
        roughnessMap: plowedRoughnessTexture,
        aoMap: plowedAoTexture,
        displacementMap: plowedDisplacementTexture,
        displacementScale: 0.12, // Más pronunciado para simular tierra removida
        aoMapIntensity: 1.3, // Más contraste para resaltar la textura removida
        roughness: 0.9, // Más rugosa que la tierra normal
        // Removido el color override para usar el color natural de la textura
    });

    // Crear material para previsualización
    previewMaterial = new THREE.MeshStandardMaterial({
        color: 0xffff00, // Amarillo para previsualización
        transparent: true,
        opacity: 0.3,
        wireframe: false,
    });
};

// Función para crear el terreno base
const createTerrain = () => {
    // Crear el plano base del terreno con más subdivisiones para mejor displacement
    const terrainGeometry = new THREE.PlaneGeometry(50, 50, 256, 256);

    terrain = new THREE.Mesh(terrainGeometry, normalTerrainMaterial);
    terrain.rotation.x = -Math.PI / 2; // Rotar para que sea horizontal
    terrain.position.y = 0;
    terrain.receiveShadow = true;
    terrain.name = 'terrain'; // Importante para el raycasting
    scene.add(terrain);
};

// Función para crear/actualizar la previsualización
const updatePreview = (x: number, z: number) => {
    if (!props.plowMode) {
        if (previewTile) {
            scene.remove(previewTile);
            previewTile = null;
        }
        return;
    }

    const tileKey = `${Math.floor(x / tileSize)}_${Math.floor(z / tileSize)}`;

    // No mostrar preview si ya está arada
    if (plowedAreas.has(tileKey)) {
        if (previewTile) {
            scene.remove(previewTile);
            previewTile = null;
        }
        return;
    }

    const tileX = Math.floor(x / tileSize) * tileSize;
    const tileZ = Math.floor(z / tileSize) * tileSize;

    if (previewTile) {
        previewTile.position.set(tileX, 0.03, tileZ);
    } else {
        const previewGeometry = new THREE.PlaneGeometry(tileSize, tileSize);
        previewTile = new THREE.Mesh(previewGeometry, previewMaterial);
        previewTile.rotation.x = -Math.PI / 2;
        previewTile.position.set(tileX, 0.03, tileZ);
        scene.add(previewTile);
    }
};

// Función para crear una parcela arada con efecto de tierra removida
const createPlowedTile = (x: number, z: number) => {
    const tileKey = `${Math.floor(x / tileSize)}_${Math.floor(z / tileSize)}`;

    if (plowedAreas.has(tileKey)) {
        return; // Ya existe una parcela arada aquí
    }

    // Crear geometría para la parcela arada con más subdivisiones para mejor detalle
    const tileGeometry = new THREE.PlaneGeometry(tileSize, tileSize, 64, 64);
    const plowedTile = new THREE.Mesh(tileGeometry, plowedTerrainMaterial);

    // Posicionar la parcela en el centro de la cuadrícula
    const tileX = Math.floor(x / tileSize) * tileSize;
    const tileZ = Math.floor(z / tileSize) * tileSize;

    plowedTile.rotation.x = -Math.PI / 2;
    plowedTile.position.set(tileX, 0.02, tileZ); // Ligeramente por encima del terreno base
    plowedTile.receiveShadow = true;
    plowedTile.castShadow = false;

    // Agregar un poco de variación aleatoria en la altura para simular tierra removida
    const vertices = tileGeometry.attributes.position.array;
    for (let i = 2; i < vertices.length; i += 3) {
        vertices[i] += (Math.random() - 0.5) * 0.05; // Variación sutil en Y
    }
    tileGeometry.attributes.position.needsUpdate = true;
    tileGeometry.computeVertexNormals(); // Recalcular normales para mejor iluminación

    scene.add(plowedTile);
    plowedAreas.set(tileKey, plowedTile);
    plowedTiles.value.add(tileKey);

    console.log(
        `🚜 Tierra arada en parcela: ${tileKey} (x=${tileX.toFixed(1)}, z=${tileZ.toFixed(1)})`,
    );
};

// Función para manejar clics del mouse
const onMouseClick = (event: MouseEvent) => {
    // Solo procesar clic izquierdo y solo si algún modo está activo
    if (
        event.button !== 0 ||
        (!props.plowMode &&
            !props.signMode &&
            !props.potatoMode &&
            !props.alfalfaMode &&
            !props.rainMode &&
            !props.sprinklerMode &&
            !props.harvestMode)
    )
        return;

    event.preventDefault();

    if (!sceneContainer.value) return;

    // Calcular coordenadas del mouse normalizadas (-1 a +1)
    const rect = sceneContainer.value.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    // Actualizar el raycaster
    raycaster.setFromCamera(mouse, camera);

    // Si estamos en modo cosecha, buscar intersecciones con las papas plantadas
    if (props.harvestMode) {
        const potatoIntersects = raycaster.intersectObjects(
            plantedPotatoes.map(potato => potato.children).flat()
        );
        
        if (potatoIntersects.length > 0) {
            // Encontrar la papa padre del objeto intersectado
            const intersectedObject = potatoIntersects[0].object;
            const potatoGroup = plantedPotatoes.find(potato => 
                potato.children.includes(intersectedObject as THREE.Object3D)
            );
            
            if (potatoGroup) {
                harvestPotato(potatoGroup);
                return;
            }
        }
    }

    // Buscar intersecciones con el terreno para otros modos
    const intersects = raycaster.intersectObject(terrain);

    if (intersects.length > 0) {
        const intersect = intersects[0];
        const point = intersect.point;

        // Verificar que el punto esté dentro de los límites del terreno
        if (Math.abs(point.x) <= 25 && Math.abs(point.z) <= 25) {
            if (props.plowMode) {
                createPlowedTile(point.x, point.z);
            } else if (props.signMode && props.signText) {
                // Verificar que no haya un letrero muy cerca antes de crear
                const minDistance = 2;
                const tooClose = placedSigns.some((sign) => {
                    const distance = Math.sqrt(
                        Math.pow(sign.position.x - point.x, 2) +
                            Math.pow(sign.position.z - point.z, 2),
                    );
                    return distance < minDistance;
                });

                if (!tooClose) {
                    createSign(point.x, point.z, props.signText);
                }
            } else if (props.potatoMode) {
                createPotato(point.x, point.z);
            } else if (props.alfalfaMode) {
                createAlfalfa(point.x, point.z);
            } else if (props.rainMode) {
                // Activar lluvia localizada en el punto clickeado
                createLocalizedRain(point.x, point.z);
            } else if (props.sprinklerMode) {
                createSprinkler(point.x, point.z);
            }
        }
    }
};

// Función para crear/actualizar la previsualización de letrero
const updateSignPreview = (x: number, z: number) => {
    if (!props.signMode) {
        if (previewSign) {
            scene.remove(previewSign);
            previewSign = null;
        }
        return;
    }

    // Verificar que no haya un letrero muy cerca
    const minDistance = 2; // Distancia mínima entre letreros
    const tooClose = placedSigns.some((sign) => {
        const distance = Math.sqrt(
            Math.pow(sign.position.x - x, 2) + Math.pow(sign.position.z - z, 2),
        );
        return distance < minDistance;
    });

    if (tooClose) {
        if (previewSign) {
            scene.remove(previewSign);
            previewSign = null;
        }
        return;
    }

    if (previewSign) {
        previewSign.position.set(x, 0, z);
    } else {
        // Crear previsualización del letrero (más pequeño)
        previewSign = new THREE.Group();

        // Poste de previsualización (más pequeño)
        const postGeometry = new THREE.CylinderGeometry(0.03, 0.03, 1.2);
        const postMaterial = new THREE.MeshBasicMaterial({
            color: 0xffff00,
            transparent: true,
            opacity: 0.5,
        });
        const post = new THREE.Mesh(postGeometry, postMaterial);
        post.position.set(0, 0.6, 0);
        previewSign.add(post);

        // Tablero de previsualización (más pequeño)
        const boardGeometry = new THREE.BoxGeometry(0.8, 0.4, 0.05);
        const boardMaterial = new THREE.MeshBasicMaterial({
            color: 0xffff00,
            transparent: true,
            opacity: 0.3,
            wireframe: true,
        });
        const board = new THREE.Mesh(boardGeometry, boardMaterial);
        board.position.set(0, 1.0, 0);
        previewSign.add(board);

        previewSign.position.set(x, 0, z);
        scene.add(previewSign);
    }
};

// Función para crear/actualizar la previsualización de papa
const updatePotatoPreview = (x: number, z: number) => {
    if (!props.potatoMode || !potatoModel) {
        if (previewPotato) {
            scene.remove(previewPotato);
            previewPotato = null;
        }
        return;
    }

    // Verificar que estemos en terreno arado
    const tileKey = `${Math.floor(x / tileSize)}_${Math.floor(z / tileSize)}`;
    if (!plowedAreas.has(tileKey)) {
        if (previewPotato) {
            scene.remove(previewPotato);
            previewPotato = null;
        }
        return;
    }

    // Verificar que no haya papas muy cerca
    const minDistance = 1.5;
    const tooClose = plantedPotatoes.some((potato) => {
        const distance = Math.sqrt(
            Math.pow(potato.position.x - x, 2) +
                Math.pow(potato.position.z - z, 2),
        );
        return distance < minDistance;
    });

    if (tooClose) {
        if (previewPotato) {
            scene.remove(previewPotato);
            previewPotato = null;
        }
        return;
    }

    if (previewPotato) {
        previewPotato.position.set(x, 0, z);
    } else {
        // Crear previsualización de papa
        previewPotato = potatoModel.clone();

        // Hacer la previsualización semi-transparente
        previewPotato.traverse((child) => {
            if (child instanceof THREE.Mesh) {
                const material = child.material.clone();
                material.transparent = true;
                material.opacity = 0.5;
                child.material = material;
            }
        });

        previewPotato.position.set(x, 0, z);
        scene.add(previewPotato);
    }
};

// Función para manejar movimiento del mouse (previsualización)
const onMouseMove = (event: MouseEvent) => {
    if (
        (!props.plowMode &&
            !props.signMode &&
            !props.potatoMode &&
            !props.alfalfaMode &&
            !props.sprinklerMode) ||
        !sceneContainer.value
    )
        return;

    // Calcular coordenadas del mouse normalizadas (-1 a +1)
    const rect = sceneContainer.value.getBoundingClientRect();
    mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
    mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;

    // Actualizar el raycaster
    raycaster.setFromCamera(mouse, camera);

    // Buscar intersecciones con el terreno
    const intersects = raycaster.intersectObject(terrain);

    if (intersects.length > 0) {
        const intersect = intersects[0];
        const point = intersect.point;

        // Verificar que el punto esté dentro de los límites del terreno
        if (Math.abs(point.x) <= 25 && Math.abs(point.z) <= 25) {
            if (props.plowMode) {
                updatePreview(point.x, point.z);
            } else if (props.signMode) {
                updateSignPreview(point.x, point.z);
            } else if (props.potatoMode) {
                updatePotatoPreview(point.x, point.z);
            } else if (props.alfalfaMode) {
                updateAlfalfaPreview(point.x, point.z);
            } else if (props.sprinklerMode) {
                updateSprinklerPreview(point.x, point.z);
            }
        }
    }
};

// Función para prevenir el menú contextual del navegador
const onContextMenu = (event: MouseEvent) => {
    event.preventDefault();
};

// Función para crear casas realistas usando GLTF
const createRealisticHouses = async () => {
    setLoadingMessage('Cargando casa...');

    // Solo crear una casa en el centro
    const housePosition = { x: 0, z: 0 };

    try {
        const gltf = await gltfLoader.loadAsync(
            '/models/simple_farm_house_gltf/scene.gltf',
        );
        const house = gltf.scene.clone();

        // Escalar extremadamente pequeño para que se vea principalmente el terreno
        house.scale.set(0.2, 0.2, 0.2);
        house.position.set(housePosition.x, 0.02, housePosition.z);

        scene.add(house);
        updateLoadingProgress();
        console.log('Casa GLTF cargada exitosamente');
    } catch (error) {
        console.error('Error cargando casa GLTF:', error);
        // Fallback: crear casa básica si falla la carga
        createBasicHouse(housePosition.x, housePosition.z, 0);
        updateLoadingProgress();
    }
};

// Función para cargar el modelo de papa
const loadPotatoModel = async () => {
    setLoadingMessage('Cargando modelo de papa...');

    try {
        const gltf = await gltfLoader.loadAsync(
            '/models/potato_plants_collection/scene.gltf',
        );
        potatoModel = gltf.scene.clone();

        // Escalar el modelo para que sea pequeño
        potatoModel.scale.set(0.03, 0.03, 0.03);

        // Habilitar sombras
        potatoModel.traverse((child) => {
            if (child instanceof THREE.Mesh) {
                child.castShadow = true;
                child.receiveShadow = true;
            }
        });

        updateLoadingProgress();
        console.log('Modelo de papa cargado exitosamente');
    } catch (error) {
        console.error('Error cargando modelo de papa:', error);
        updateLoadingProgress();
    }
};

// Función para crear una papa plantada
const createPotato = (x: number, z: number) => {
    if (!potatoModel) {
        console.warn('Modelo de papa no cargado');
        return;
    }

    // Verificar que estemos en terreno arado
    const tileKey = `${Math.floor(x / tileSize)}_${Math.floor(z / tileSize)}`;
    if (!plowedAreas.has(tileKey)) {
        console.warn('Solo se pueden plantar papas en terreno arado');
        return;
    }

    // Verificar que no haya papas muy cerca del punto central
    const minDistance = 1.2;
    const tooClose = plantedPotatoes.some((potato) => {
        const distance = Math.sqrt(
            Math.pow(potato.position.x - x, 2) +
                Math.pow(potato.position.z - z, 2),
        );
        return distance < minDistance;
    });

    if (tooClose) {
        console.warn('Hay papas muy cerca, no se puede plantar aquí');
        return;
    }

    // Crear una colección de papas (6-10 papas en un patrón)
    const numPotatoes = 6 + Math.floor(Math.random() * 5); // Entre 6 y 10 papas
    const radius = 1.2; // Radio del círculo donde se distribuyen las papas

    for (let i = 0; i < numPotatoes; i++) {
        // Distribuir las papas en un patrón circular con algo de aleatoriedad
        const angle =
            (i / numPotatoes) * Math.PI * 2 + (Math.random() - 0.5) * 0.5;
        const distance = radius * (0.3 + Math.random() * 0.7); // Variación en la distancia

        const potatoX = x + Math.cos(angle) * distance;
        const potatoZ = z + Math.sin(angle) * distance;

        // Crear la papa individual
        const potato = potatoModel.clone();
        potato.position.set(potatoX, 0, potatoZ);

        // Añadir variación aleatoria en rotación y escala
        potato.rotation.y = Math.random() * Math.PI * 2;
        const scaleVariation = 0.8 + Math.random() * 0.4; // Escala entre 0.8 y 1.2
        potato.scale.multiplyScalar(scaleVariation);

        scene.add(potato);
        plantedPotatoes.push(potato);
    }

    console.log(
        `🥔 Colección de ${numPotatoes} papas plantadas en (${x.toFixed(1)}, ${z.toFixed(1)})`,
    );
};

// Función para cosechar una papa
const harvestPotato = (potatoGroup: THREE.Group) => {
    // Calcular la calidad basada en factores aleatorios (simulando condiciones de crecimiento)
    const qualityRoll = Math.random();
    let quality = 'Buena';
    let xpGained = 25;
    let potatoCount = 3;

    if (qualityRoll > 0.8) {
        quality = 'Excelente';
        xpGained = 50;
        potatoCount = 5;
    } else if (qualityRoll > 0.6) {
        quality = 'Muy Buena';
        xpGained = 35;
        potatoCount = 4;
    } else if (qualityRoll < 0.2) {
        quality = 'Regular';
        xpGained = 15;
        potatoCount = 2;
    }

    // Crear efecto de partículas de cosecha
    createHarvestEffect(potatoGroup.position.x, potatoGroup.position.z);

    // Remover la papa de la escena y del array
    scene.remove(potatoGroup);
    const index = plantedPotatoes.indexOf(potatoGroup);
    if (index > -1) {
        plantedPotatoes.splice(index, 1);
    }

    // Emitir evento de cosecha al componente padre
    emit('potatoHarvested', potatoCount, quality, xpGained);

    console.log(`🌾 Papa cosechada: ${potatoCount} papas de calidad ${quality}, +${xpGained} XP`);
};

// Función para crear efecto visual de cosecha
const createHarvestEffect = (x: number, z: number) => {
    // Crear partículas doradas que se eleven
    const particleCount = 20;
    const particles = new THREE.BufferGeometry();
    const positions = new Float32Array(particleCount * 3);
    const velocities = new Float32Array(particleCount * 3);

    for (let i = 0; i < particleCount; i++) {
        const i3 = i * 3;
        // Posición inicial alrededor del punto de cosecha
        positions[i3] = x + (Math.random() - 0.5) * 2;
        positions[i3 + 1] = 0.5;
        positions[i3 + 2] = z + (Math.random() - 0.5) * 2;

        // Velocidades aleatorias hacia arriba
        velocities[i3] = (Math.random() - 0.5) * 0.1;
        velocities[i3 + 1] = Math.random() * 0.2 + 0.1;
        velocities[i3 + 2] = (Math.random() - 0.5) * 0.1;
    }

    particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    particles.setAttribute('velocity', new THREE.BufferAttribute(velocities, 3));

    const particleMaterial = new THREE.PointsMaterial({
        color: 0xffd700, // Dorado
        size: 0.1,
        transparent: true,
        opacity: 0.8,
    });

    const particleSystem = new THREE.Points(particles, particleMaterial);
    scene.add(particleSystem);

    // Animar las partículas por 2 segundos
    let animationTime = 0;
    const animateParticles = () => {
        animationTime += 0.016;
        
        const positions = particleSystem.geometry.attributes.position.array as Float32Array;
        const velocities = particleSystem.geometry.attributes.velocity.array as Float32Array;

        for (let i = 0; i < particleCount; i++) {
            const i3 = i * 3;
            positions[i3] += velocities[i3];
            positions[i3 + 1] += velocities[i3 + 1];
            positions[i3 + 2] += velocities[i3 + 2];

            // Aplicar gravedad
            velocities[i3 + 1] -= 0.005;
        }

        particleSystem.geometry.attributes.position.needsUpdate = true;
        particleMaterial.opacity = Math.max(0, 0.8 - animationTime * 0.4);

        if (animationTime < 2) {
            requestAnimationFrame(animateParticles);
        } else {
            scene.remove(particleSystem);
        }
    };

    animateParticles();
};

// Función para crear alfalfa (similar a createPotato)
const createAlfalfa = (x: number, z: number) => {
    if (!alfalfaModel) {
        console.warn('Modelo de alfalfa no cargado');
        return;
    }

    // Verificar que estemos en terreno arado
    const tileKey = `${Math.floor(x / tileSize)}_${Math.floor(z / tileSize)}`;
    if (!plowedAreas.has(tileKey)) {
        console.warn('Solo se puede plantar alfalfa en terreno arado');
        return;
    }

    // Verificar que no haya alfalfa muy cerca del punto central
    const minDistance = 1.2;
    const tooClose = plantedAlfalfas.some((alfalfa) => {
        const distance = Math.sqrt(
            Math.pow(alfalfa.position.x - x, 2) +
                Math.pow(alfalfa.position.z - z, 2),
        );
        return distance < minDistance;
    });

    if (tooClose) {
        console.warn('Hay alfalfa muy cerca, no se puede plantar aquí');
        return;
    }

    // Crear una colección de alfalfa (15-25 plantas en un patrón)
    const numAlfalfas = 15 + Math.floor(Math.random() * 11); // Entre 15 y 25 plantas
    const radius = 1.5; // Radio del círculo donde se distribuyen las plantas

    for (let i = 0; i < numAlfalfas; i++) {
        // Distribuir las plantas en un patrón circular con algo de aleatoriedad
        const angle =
            (i / numAlfalfas) * Math.PI * 2 + (Math.random() - 0.5) * 0.5;
        const distance = radius * (0.2 + Math.random() * 0.8); // Variación en la distancia

        const alfalfaX = x + Math.cos(angle) * distance;
        const alfalfaZ = z + Math.sin(angle) * distance;

        // Crear la planta individual
        const alfalfa = alfalfaModel.clone();
        alfalfa.position.set(alfalfaX, 0, alfalfaZ);

        // Añadir variación aleatoria en rotación y escala (mucho más pequeña)
        alfalfa.rotation.y = Math.random() * Math.PI * 2;
        const scaleVariation = 0.3 + Math.random() * 0.4; // Escala entre 0.3 y 0.7 (mucho más pequeña)
        alfalfa.scale.multiplyScalar(scaleVariation);

        scene.add(alfalfa);
        plantedAlfalfas.push(alfalfa);
    }

    console.log(
        `🍀 Colección de ${numAlfalfas} plantas de alfalfa plantadas en (${x.toFixed(1)}, ${z.toFixed(1)})`,
    );
};

// Función para crear un aspersor
const createSprinkler = async (x: number, z: number) => {
    if (!sprinklerModel) {
        console.warn('⚠️ Modelo de aspersor no cargado');
        return;
    }

    // Verificar que esté dentro de los límites del terreno
    if (x < -10 || x > 10 || z < -10 || z > 10) {
        console.warn('⚠️ No se puede colocar aspersor fuera del terreno');
        return;
    }

    // Crear el aspersor exactamente donde se hace clic (sin restricciones de distancia)
    const sprinkler = sprinklerModel.clone();
    sprinkler.position.set(x, 0, z);

    // Añadir variación aleatoria en rotación
    sprinkler.rotation.y = Math.random() * Math.PI * 2;

    // Habilitar sombras
    sprinkler.traverse((child) => {
        if (child instanceof THREE.Mesh) {
            child.castShadow = true;
            child.receiveShadow = true;
        }
    });

    scene.add(sprinkler);
    placedSprinklers.push(sprinkler);

    console.log(`💧 Aspersor colocado en (${x.toFixed(1)}, ${z.toFixed(1)})`);
};

// Función para actualizar la vista previa del aspersor
const updateSprinklerPreview = (x: number, z: number) => {
    if (!sprinklerModel) return;

    // Remover vista previa anterior
    if (previewSprinkler) {
        scene.remove(previewSprinkler);
        previewSprinkler = null;
    }

    // Verificar que esté dentro de los límites del terreno
    if (x < -10 || x > 10 || z < -10 || z > 10) {
        return;
    }

    // Verificar distancia mínima con otros aspersores
    const minDistance = 3;
    let tooClose = false;
    for (const sprinkler of placedSprinklers) {
        const distance = Math.sqrt(
            Math.pow(sprinkler.position.x - x, 2) +
                Math.pow(sprinkler.position.z - z, 2),
        );
        if (distance < minDistance) {
            tooClose = true;
            break;
        }
    }

    // Crear vista previa
    previewSprinkler = sprinklerModel.clone();
    previewSprinkler.position.set(x, 0, z);

    // Hacer semi-transparente y cambiar color según validez
    previewSprinkler.traverse((child) => {
        if (child instanceof THREE.Mesh) {
            const material = child.material.clone();
            material.transparent = true;
            material.opacity = 0.6;

            // Rojo si está demasiado cerca, verde si es válido
            if (tooClose) {
                material.color.setHex(0xff0000);
            } else {
                material.color.setHex(0x00ff00);
            }

            child.material = material;
        }
    });

    scene.add(previewSprinkler);
};

// Función de respaldo para crear casas básicas
const createBasicHouse = (x: number, z: number, index: number) => {
    const colors = [0xff6b6b, 0x4ecdc4, 0x45b7d1, 0xf9ca24];

    // Casa principal - extremadamente pequeña
    const houseGeometry = new THREE.BoxGeometry(0.4, 0.3, 0.4);
    const houseMaterial = new THREE.MeshLambertMaterial({ color: 0xd4a574 });
    const house = new THREE.Mesh(houseGeometry, houseMaterial);
    house.position.set(x, 0.15, z);

    // Techo - extremadamente pequeño
    const roofGeometry = new THREE.ConeGeometry(0.3, 0.25, 4);
    const roofMaterial = new THREE.MeshLambertMaterial({
        color: colors[index],
    });
    const roof = new THREE.Mesh(roofGeometry, roofMaterial);
    roof.position.set(x, 0.4, z);
    roof.rotation.y = Math.PI / 4;

    scene.add(house);
    scene.add(roof);
};

// Función para crear un letrero con texto personalizado
const createSign = async (x: number, z: number, text: string) => {
    try {
        setLoadingMessage('Cargando letrero...');

        // Cargar el modelo GLTF del letrero
        const gltf = await gltfLoader.loadAsync(
            '/componenetes/wooden_sign_with_roof_gltf/scene.gltf',
        );
        const signGroup = new THREE.Group();

        // Clonar el modelo del letrero (más pequeño)
        const signModel = gltf.scene.clone();
        signModel.scale.set(0.3, 0.3, 0.3); // Reducir significativamente el tamaño
        signGroup.add(signModel);

        // Crear el texto usando canvas para mejor visibilidad
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.width = 256;
        canvas.height = 64;

        // Configurar el contexto del canvas
        context.fillStyle = '#8B4513'; // Fondo marrón claro
        context.fillRect(0, 0, canvas.width, canvas.height);

        // Configurar el texto
        context.fillStyle = '#FFFFFF'; // Texto blanco para contraste
        context.font = 'bold 20px Arial';
        context.textAlign = 'center';
        context.textBaseline = 'middle';

        // Dibujar el texto
        context.fillText(text, canvas.width / 2, canvas.height / 2);

        // Crear textura del canvas
        const textTexture = new THREE.CanvasTexture(canvas);
        textTexture.needsUpdate = true;

        // Crear geometría de texto
        const textGeometry = new THREE.PlaneGeometry(0.6, 0.15);
        const textMaterial = new THREE.MeshBasicMaterial({
            map: textTexture,
            transparent: true,
            alphaTest: 0.1,
        });

        const textMesh = new THREE.Mesh(textGeometry, textMaterial);
        textMesh.position.set(0, 0.8, 0.03); // Posicionar en el letrero más pequeño
        textMesh.rotation.y = 0; // Orientar hacia el frente

        signGroup.add(textMesh);

        // Posicionar el grupo del letrero
        signGroup.position.set(x, 0, z);

        // Habilitar sombras
        signGroup.traverse((child) => {
            if (child instanceof THREE.Mesh) {
                child.castShadow = true;
                child.receiveShadow = true;
            }
        });

        scene.add(signGroup);
        placedSigns.push(signGroup);

        console.log(
            `🪧 Letrero colocado en (${x.toFixed(1)}, ${z.toFixed(1)}) con texto: "${text}"`,
        );

        // Emitir evento de letrero colocado
        emit('signPlaced');

        return signGroup;
    } catch (error) {
        console.error('Error cargando letrero GLTF:', error);

        // Fallback: crear letrero básico si falla la carga
        const fallbackSign = createBasicSign(x, z, text);
        emit('signPlaced');
        return fallbackSign;
    }
};

// Función de respaldo para crear letrero básico (más pequeño y con mejor texto)
const createBasicSign = (x: number, z: number, text: string) => {
    const signGroup = new THREE.Group();

    // Poste del letrero (más pequeño)
    const postGeometry = new THREE.CylinderGeometry(0.03, 0.03, 1.2);
    const postMaterial = new THREE.MeshStandardMaterial({ color: 0x8b4513 });
    const post = new THREE.Mesh(postGeometry, postMaterial);
    post.position.set(0, 0.6, 0);
    post.castShadow = true;
    signGroup.add(post);

    // Tablero del letrero (más pequeño)
    const boardGeometry = new THREE.BoxGeometry(0.8, 0.4, 0.05);
    const boardMaterial = new THREE.MeshStandardMaterial({ color: 0xdeb887 });
    const board = new THREE.Mesh(boardGeometry, boardMaterial);
    board.position.set(0, 1.0, 0);
    board.castShadow = true;
    board.receiveShadow = true;
    signGroup.add(board);

    // Crear texto usando canvas para mejor visibilidad
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    canvas.width = 256;
    canvas.height = 64;

    // Configurar el contexto del canvas
    context.fillStyle = '#DEB887'; // Fondo del color del tablero
    context.fillRect(0, 0, canvas.width, canvas.height);

    // Configurar el texto
    context.fillStyle = '#2F1B14'; // Texto marrón oscuro para contraste
    context.font = 'bold 18px Arial';
    context.textAlign = 'center';
    context.textBaseline = 'middle';

    // Dibujar el texto
    context.fillText(text, canvas.width / 2, canvas.height / 2);

    // Crear textura del canvas
    const textTexture = new THREE.CanvasTexture(canvas);
    textTexture.needsUpdate = true;

    // Texto con textura del canvas
    const textGeometry = new THREE.PlaneGeometry(0.75, 0.35);
    const textMaterial = new THREE.MeshBasicMaterial({
        map: textTexture,
        transparent: true,
        alphaTest: 0.1,
    });
    const textPlane = new THREE.Mesh(textGeometry, textMaterial);
    textPlane.position.set(0, 1.0, 0.03);
    signGroup.add(textPlane);

    // Posicionar el grupo
    signGroup.position.set(x, 0, z);

    scene.add(signGroup);
    placedSigns.push(signGroup);

    console.log(
        `🪧 Letrero básico colocado en (${x.toFixed(1)}, ${z.toFixed(1)}) con texto: "${text}"`,
    );

    return signGroup;
};

// Función para crear árboles realistas usando GLTF
const createRealisticTrees = async () => {
    setLoadingMessage('Cargando árboles realistas...');

    // Posiciones estáticas dentro del mapa (50x50, límites ±22)
    const treePositions = [
        { x: -8, z: 6, scale: 0.6, rotation: 0.5 }, // esquina superior izquierda
        { x: 8, z: -6, scale: 0.7, rotation: 1.2 }, // esquina inferior derecha
        { x: -7, z: 3, scale: 0.55, rotation: 2.8 }, // lado izquierdo superior
        { x: 7, z: -3, scale: 0.65, rotation: 2.5 }, // lado derecho inferior
        { x: -3, z: -7, scale: 0.6, rotation: 3.7 }, // lado inferior izquierdo
        { x: 3, z: 7, scale: 0.75, rotation: 1.9 }, // lado superior derecho
        { x: -6, z: -4, scale: 0.8, rotation: 5.1 }, // esquina inferior izquierda
        { x: 6, z: 4, scale: 0.7, rotation: 0.3 }, // esquina superior derecha
    ];

    try {
        const gltf = await gltfLoader.loadAsync(
            '/models/realistic_tree/scene.gltf',
        );

        treePositions.forEach((pos) => {
            const tree = gltf.scene.clone();

            // Escalar con valor estático predefinido
            tree.scale.set(pos.scale, pos.scale, pos.scale);
            tree.position.set(pos.x, 0, pos.z);

            // Rotación estática predefinida
            tree.rotation.y = pos.rotation;

            // Habilitar sombras
            tree.traverse((child) => {
                if (child instanceof THREE.Mesh) {
                    child.castShadow = true;
                    child.receiveShadow = true;
                }
            });

            scene.add(tree);
        });

        updateLoadingProgress();
        console.log('Árboles GLTF cargados exitosamente');
    } catch (error) {
        console.error('Error cargando árboles GLTF:', error);
        // Fallback: crear árboles básicos si falla la carga
        createBasicTrees();
        updateLoadingProgress();
    }
};

// Función de respaldo para crear árboles básicos
const createBasicTrees = () => {
    const treePositions = [
        { x: -18, z: 15 }, // Esquina superior izquierda
        { x: 18, z: -15 }, // Esquina inferior derecha
        { x: -20, z: 5 }, // Lado izquierdo superior
        { x: 20, z: -5 }, // Lado derecho inferior
        { x: -5, z: -20 }, // Lado inferior izquierdo
        { x: 5, z: 10 }, // Lado superior derecho
        { x: -15, z: -10 }, // Esquina inferior izquierda
        { x: 15, z: 10 }, // Esquina superior derecha
    ];

    treePositions.forEach((pos) => {
        // Tronco
        const trunkGeometry = new THREE.CylinderGeometry(0.3, 0.4, 3);
        const trunkMaterial = new THREE.MeshStandardMaterial({
            color: 0x8b4513, // Marrón
        });

        const trunk = new THREE.Mesh(trunkGeometry, trunkMaterial);
        trunk.position.set(pos.x, 1.5, pos.z);
        trunk.castShadow = true;
        trunk.receiveShadow = true;
        scene.add(trunk);

        // Copa del árbol
        const foliageGeometry = new THREE.SphereGeometry(2);
        const foliageMaterial = new THREE.MeshStandardMaterial({
            color: 0x228b22, // Verde bosque
        });

        const foliage = new THREE.Mesh(foliageGeometry, foliageMaterial);
        foliage.position.set(pos.x, 3.5, pos.z);
        foliage.castShadow = true;
        foliage.receiveShadow = true;
        scene.add(foliage);
    });
};

// Función de inicialización
const initScene = async () => {
    if (!sceneContainer.value) return;

    // Crear escena
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0x87ceeb); // Azul cielo

    // Crear cámara
    camera = new THREE.PerspectiveCamera(
        75,
        sceneContainer.value.clientWidth / sceneContainer.value.clientHeight,
        0.1,
        1000,
    );

    // Posicionar cámara para vista isométrica
    camera.position.set(15, 12, 15);
    camera.lookAt(0, 0, 0);

    // Crear renderer
    renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(
        sceneContainer.value.clientWidth,
        sceneContainer.value.clientHeight,
    );
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;

    sceneContainer.value.appendChild(renderer.domElement);

    // Inicializar sistemas de interacción
    raycaster = new THREE.Raycaster();
    mouse = new THREE.Vector2();

    // Agregar event listeners para interacción
    renderer.domElement.addEventListener('mousedown', onMouseClick);
    renderer.domElement.addEventListener('mousemove', onMouseMove);
    renderer.domElement.addEventListener('contextmenu', onContextMenu);

    // Inicializar loaders
    gltfLoader = new GLTFLoader();
    textureLoader = new THREE.TextureLoader();

    // Configurar controles de órbita
    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.screenSpacePanning = false;
    controls.minDistance = 5;
    controls.maxDistance = 20; // Limitar zoom out para mantener vista cercana
    controls.maxPolarAngle = Math.PI / 2.2; // Limitar ángulo para evitar ir debajo del terreno
    controls.minPolarAngle = 0.1; // Evitar vista completamente cenital

    // Agregar luces mejoradas
    // Luz ambiental más brillante para iluminación general
    ambientLight = new THREE.AmbientLight(0x404040, 0.4);
    originalAmbientIntensity = ambientLight.intensity;
    scene.add(ambientLight);

    // Luz direccional principal (sol)
    directionalLight = new THREE.DirectionalLight(0xffffff, 1.2);
    originalLightIntensity = directionalLight.intensity;
    directionalLight.position.set(20, 20, 10);
    directionalLight.castShadow = true;
    directionalLight.shadow.mapSize.width = 4096;
    directionalLight.shadow.mapSize.height = 4096;
    directionalLight.shadow.camera.near = 0.1;
    directionalLight.shadow.camera.far = 100;
    directionalLight.shadow.camera.left = -30;
    directionalLight.shadow.camera.right = 30;
    directionalLight.shadow.camera.top = 30;
    directionalLight.shadow.camera.bottom = -30;
    scene.add(directionalLight);

    // Luz hemisférica para simular luz del cielo
    const hemisphereLight = new THREE.HemisphereLight(0x87ceeb, 0x8b4513, 0.6);
    hemisphereLight.position.set(0, 20, 0);
    scene.add(hemisphereLight);

    // Luz puntual adicional para resaltar detalles
    const pointLight = new THREE.PointLight(0xffffff, 0.8, 50);
    pointLight.position.set(-10, 15, -10);
    pointLight.castShadow = true;
    scene.add(pointLight);

    // Crear elementos de la escena
    totalAssets = 13; // 10 texturas + 1 casa + 1 árbol + 1 papa
    loadedAssets = 0;

    setLoadingMessage('Iniciando carga de elementos...');

    createTerrainMaterials();
    createTerrain();
    await createRealisticHouses();
    await createRealisticTrees();
    await loadPotatoModel();
    await loadAlfalfaModel();
    await loadSprinklerModel();

    // Iniciar animación
    animate();
};

// Función para crear efectos de salpicadura
const createSplashEffect = (x: number, y: number, z: number) => {
    const splashCount = 8;
    const splashGeometry = new THREE.BufferGeometry();
    const positions = new Float32Array(splashCount * 3);

    // Crear partículas de salpicadura
    for (let i = 0; i < splashCount; i++) {
        const i3 = i * 3;
        const angle = (i / splashCount) * Math.PI * 2;
        const radius = Math.random() * 0.5 + 0.2;

        positions[i3] = x + Math.cos(angle) * radius;
        positions[i3 + 1] = y + Math.random() * 0.3;
        positions[i3 + 2] = z + Math.sin(angle) * radius;
    }

    splashGeometry.setAttribute(
        'position',
        new THREE.BufferAttribute(positions, 3),
    );

    const splashMaterial = new THREE.PointsMaterial({
        color: 0xffffff,
        size: 0.3,
        transparent: true,
        opacity: 0.8,
    });

    const splash = new THREE.Points(splashGeometry, splashMaterial);
    scene.add(splash);

    // Animar y remover salpicadura
    let opacity = 0.8;
    const animateSplash = () => {
        opacity -= 0.05;
        splashMaterial.opacity = opacity;

        if (opacity > 0) {
            requestAnimationFrame(animateSplash);
        } else {
            scene.remove(splash);
            splashGeometry.dispose();
            splashMaterial.dispose();
        }
    };

    animateSplash();
};

// Función para crear lluvia localizada en un punto específico
const createLocalizedRain = (x: number, z: number) => {
    // Crear un efecto de lluvia más intensa en el área clickeada
    const localRainCount = 500;
    const localRainGeometry = new THREE.BufferGeometry();
    const positions = new Float32Array(localRainCount * 3);
    const velocities = new Float32Array(localRainCount * 3);

    // Crear partículas concentradas alrededor del punto clickeado
    for (let i = 0; i < localRainCount; i++) {
        const i3 = i * 3;
        const radius = Math.random() * 5; // Radio de 5 unidades alrededor del punto
        const angle = Math.random() * Math.PI * 2;

        positions[i3] = x + Math.cos(angle) * radius; // x
        positions[i3 + 1] = Math.random() * 15 + 10; // y (altura)
        positions[i3 + 2] = z + Math.sin(angle) * radius; // z

        // Velocidades más rápidas para lluvia intensa
        velocities[i3] = (Math.random() - 0.5) * 1; // x
        velocities[i3 + 1] = -Math.random() * 0.8 - 0.5; // y (caída más rápida)
        velocities[i3 + 2] = (Math.random() - 0.5) * 1; // z
    }

    localRainGeometry.setAttribute(
        'position',
        new THREE.BufferAttribute(positions, 3),
    );

    // Material más brillante para lluvia localizada
    const localRainMaterial = new THREE.PointsMaterial({
        color: 0xadd8e6,
        size: 1.2,
        transparent: true,
        opacity: 0.8,
    });

    const localRainSystem = new THREE.Points(
        localRainGeometry,
        localRainMaterial,
    );
    localRainSystem.userData = { velocities, positions, startTime: Date.now() };

    scene.add(localRainSystem);

    // Crear charco en el punto clickeado
    createSinglePuddle(x, z);

    // Remover la lluvia localizada después de 3 segundos
    setTimeout(() => {
        scene.remove(localRainSystem);
        localRainGeometry.dispose();
        localRainMaterial.dispose();
    }, 3000);

    console.log(
        `🌧️ Lluvia localizada creada en (${x.toFixed(1)}, ${z.toFixed(1)})`,
    );
};

// Función para crear un charco individual
const createSinglePuddle = (x: number, z: number) => {
    const size = Math.random() * 1.5 + 1;

    const puddleGeometry = new THREE.PlaneGeometry(size, size);
    const puddleMaterial = new THREE.MeshStandardMaterial({
        color: 0x4a90e2,
        transparent: true,
        opacity: 0.8,
        roughness: 0.05,
        metalness: 0.9,
    });

    const puddle = new THREE.Mesh(puddleGeometry, puddleMaterial);
    puddle.rotation.x = -Math.PI / 2;
    puddle.position.set(x, 0.01, z);
    puddle.receiveShadow = true;

    scene.add(puddle);

    // Remover el charco después de 10 segundos
    setTimeout(() => {
        scene.remove(puddle);
        puddleGeometry.dispose();
        puddleMaterial.dispose();
    }, 10000);
};

// Función de animación
const animate = () => {
    animationId = requestAnimationFrame(animate);

    // Actualizar controles
    controls.update();

    // Animar partículas de lluvia si está lloviendo
    if (isRaining.value && rainParticleSystem && rainDrops) {
        const positions = rainGeometry.getAttribute('position');
        const colors = rainGeometry.getAttribute('color');
        const velocities = rainParticleSystem.userData.velocities;
        const time = Date.now() * 0.001; // Tiempo para efectos dinámicos

        // Animar cada línea de lluvia (cada línea tiene 2 puntos)
        for (let i = 0; i < positions.count / 2; i++) {
            const i3 = i * 3;
            const i6 = i * 6;

            // Actualizar posición del punto superior
            rainDrops[i6] += velocities[i3] * 0.8; // x con más viento
            rainDrops[i6 + 1] += velocities[i3 + 1] * 1.2; // y caída más rápida
            rainDrops[i6 + 2] += velocities[i3 + 2] * 0.8; // z

            // Actualizar posición del punto inferior (mantener longitud de línea)
            const lineLength = Math.random() * 2.5 + 1.0;
            const windEffect = Math.sin(time + i * 0.1) * 0.3; // Efecto de viento dinámico

            rainDrops[i6 + 3] = rainDrops[i6] + windEffect;
            rainDrops[i6 + 4] = rainDrops[i6 + 1] - lineLength;
            rainDrops[i6 + 5] = rainDrops[i6 + 2] + windEffect * 0.5;

            // Efecto de variación de color dinámico
            if (colors) {
                const colorVariation =
                    Math.sin(time * 2 + i * 0.05) * 0.1 + 0.9;
                colors.array[i6] = 0.7 * colorVariation;
                colors.array[i6 + 1] = 0.85 * colorVariation;
                colors.array[i6 + 2] = 0.95 * colorVariation;

                colors.array[i6 + 3] = 0.6 * colorVariation;
                colors.array[i6 + 4] = 0.75 * colorVariation;
                colors.array[i6 + 5] = 0.85 * colorVariation;
            }

            // Reiniciar partícula si sale del área o toca el suelo
            if (
                rainDrops[i6 + 1] < -2 ||
                Math.abs(rainDrops[i6]) > 50 ||
                Math.abs(rainDrops[i6 + 2]) > 50
            ) {
                // Nueva posición inicial
                const x = (Math.random() - 0.5) * 100;
                const y = Math.random() * 40 + 25;
                const z = (Math.random() - 0.5) * 100;

                rainDrops[i6] = x;
                rainDrops[i6 + 1] = y;
                rainDrops[i6 + 2] = z;

                // Actualizar punto inferior
                const newLineLength = Math.random() * 2.5 + 1.0;
                const newAngle = Math.random() * 0.3 - 0.15;

                rainDrops[i6 + 3] = x + Math.sin(newAngle) * newLineLength;
                rainDrops[i6 + 4] = y - newLineLength;
                rainDrops[i6 + 5] =
                    z + Math.cos(newAngle) * newLineLength * 0.3;

                // Nuevas velocidades
                const windStrength = Math.random() * 2 + 0.5;
                velocities[i3] = (Math.random() - 0.5) * windStrength;
                velocities[i3 + 1] = -Math.random() * 1.2 - 0.8;
                velocities[i3 + 2] = (Math.random() - 0.5) * windStrength * 0.7;

                // Crear efecto de salpicadura ocasional
                if (Math.random() < 0.1) {
                    createSplashEffect(rainDrops[i6], 0, rainDrops[i6 + 2]);
                }
            }
        }

        positions.needsUpdate = true;
        if (colors) colors.needsUpdate = true;
    }

    renderer.render(scene, camera);
};

// Función para manejar redimensionamiento
const handleResize = () => {
    if (!sceneContainer.value) return;

    camera.aspect =
        sceneContainer.value.clientWidth / sceneContainer.value.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(
        sceneContainer.value.clientWidth,
        sceneContainer.value.clientHeight,
    );
};

// Función para iniciar el juego cuando se presiona el botón
const startGame = async () => {
    gameStarted.value = true;
    await initScene();
};

onMounted(async () => {
    // Solo agregar el event listener de resize, no inicializar la escena automáticamente
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    if (animationId) {
        cancelAnimationFrame(animationId);
    }

    // Limpiar event listeners
    if (renderer && renderer.domElement) {
        renderer.domElement.removeEventListener('mousedown', onMouseClick);
        renderer.domElement.removeEventListener('mousemove', onMouseMove);
        renderer.domElement.removeEventListener('contextmenu', onContextMenu);
    }

    window.removeEventListener('resize', handleResize);

    if (renderer) {
        renderer.dispose();
    }

    if (controls) {
        controls.dispose();
    }

    // Limpiar parcelas aradas
    plowedAreas.clear();
    plowedTiles.value.clear();

    // Limpiar previsualización
    if (previewTile) {
        scene.remove(previewTile);
        previewTile = null;
    }
});

// Watch para cambios en el modo de arado
watch(
    () => props.plowMode,
    (newValue) => {
        if (!newValue && previewTile) {
            scene.remove(previewTile);
            previewTile = null;
        }
    },
);

// Watch para cambios en el modo de letrero
watch(
    () => props.signMode,
    (newValue) => {
        if (!newValue && previewSign) {
            scene.remove(previewSign);
            previewSign = null;
        }
    },
);

// Watch para cambios en el modo de papa
watch(
    () => props.potatoMode,
    (newValue) => {
        if (!newValue && previewPotato) {
            scene.remove(previewPotato);
            previewPotato = null;
        }
    },
);

// Watch para cambios en el modo de aspersor
watch(
    () => props.sprinklerMode,
    (newValue) => {
        if (!newValue && previewSprinkler) {
            scene.remove(previewSprinkler);
            previewSprinkler = null;
        }
    },
);

// Watch para cambios en el modo de lluvia
watch(
    () => props.rainMode,
    (newValue) => {
        if (newValue) {
            startRain();
        } else {
            stopRain();
        }
    },
);

// Función para crear el sistema de partículas de lluvia
const createRainSystem = () => {
    const particleCount = 2500; // Más partículas para mayor densidad

    // Crear geometría de líneas para lluvia realista
    rainGeometry = new THREE.BufferGeometry();

    // Arrays para las líneas de lluvia (cada línea necesita 2 puntos)
    const positions = new Float32Array(particleCount * 6); // 2 puntos * 3 coordenadas
    const velocities = new Float32Array(particleCount * 3);
    const colors = new Float32Array(particleCount * 6); // Colores para cada vértice
    const sizes = new Float32Array(particleCount); // Tamaños variables

    // Inicializar líneas de lluvia
    for (let i = 0; i < particleCount; i++) {
        const i3 = i * 3;
        const i6 = i * 6;

        // Posición inicial aleatoria en área más amplia
        const x = (Math.random() - 0.5) * 100;
        const y = Math.random() * 40 + 25;
        const z = (Math.random() - 0.5) * 100;

        // Longitud variable de la línea de lluvia (gotas más largas)
        const lineLength = Math.random() * 2.5 + 1.0;
        const angle = Math.random() * 0.3 - 0.15; // Ligera inclinación por viento

        // Punto superior de la línea
        positions[i6] = x;
        positions[i6 + 1] = y;
        positions[i6 + 2] = z;

        // Punto inferior de la línea con inclinación
        positions[i6 + 3] = x + Math.sin(angle) * lineLength;
        positions[i6 + 4] = y - lineLength;
        positions[i6 + 5] = z + Math.cos(angle) * lineLength * 0.3;

        // Velocidades más variadas y realistas
        const windStrength = Math.random() * 2 + 0.5;
        velocities[i3] = (Math.random() - 0.5) * windStrength; // x (viento variable)
        velocities[i3 + 1] = -Math.random() * 1.2 - 0.8; // y (caída más rápida)
        velocities[i3 + 2] = (Math.random() - 0.5) * windStrength * 0.7; // z (viento cruzado)

        // Colores variables para mayor realismo (azul claro a blanco)
        const colorIntensity = Math.random() * 0.4 + 0.6;
        const r = 0.6 + colorIntensity * 0.4;
        const g = 0.8 + colorIntensity * 0.2;
        const b = 0.9 + colorIntensity * 0.1;

        // Color para ambos puntos de la línea
        colors[i6] = r;
        colors[i6 + 1] = g;
        colors[i6 + 2] = b;
        colors[i6 + 3] = r * 0.8;
        colors[i6 + 4] = g * 0.8;
        colors[i6 + 5] = b * 0.8;

        // Tamaño variable
        sizes[i] = Math.random() * 0.5 + 0.5;
    }

    rainGeometry.setAttribute(
        'position',
        new THREE.BufferAttribute(positions, 3),
    );
    rainGeometry.setAttribute('color', new THREE.BufferAttribute(colors, 3));
    rainDrops = positions;

    // Material mejorado para líneas de lluvia más realistas
    rainMaterial = new THREE.LineBasicMaterial({
        vertexColors: true, // Usar colores por vértice
        transparent: true,
        opacity: 0.8,
        linewidth: 1.5,
        fog: true, // Responder a la niebla
    });

    // Crear el sistema usando LineSegments para líneas individuales
    rainParticleSystem = new THREE.LineSegments(rainGeometry, rainMaterial);

    // Almacenar datos adicionales
    rainParticleSystem.userData = {
        velocities,
        colors,
        sizes,
        originalPositions: positions.slice(), // Copia para reset
    };
};

// Función para inicializar sonidos de lluvia
const initRainSounds = () => {
    if (!soundsEnabled.value) return;

    try {
        // Crear audio de lluvia usando Web Audio API para mejor control
        const audioContext = new (window.AudioContext ||
            (window as any).webkitAudioContext)();

        // Generar sonido de lluvia procedural
        const bufferSize = audioContext.sampleRate * 2; // 2 segundos de audio
        const buffer = audioContext.createBuffer(
            1,
            bufferSize,
            audioContext.sampleRate,
        );
        const data = buffer.getChannelData(0);

        // Generar ruido blanco filtrado para simular lluvia
        for (let i = 0; i < bufferSize; i++) {
            data[i] = (Math.random() * 2 - 1) * 0.1; // Ruido suave
        }

        // Crear fuente de audio
        const source = audioContext.createBufferSource();
        source.buffer = buffer;
        source.loop = true;

        // Crear filtros para el sonido de lluvia
        const lowpass = audioContext.createBiquadFilter();
        lowpass.type = 'lowpass';
        lowpass.frequency.value = 2000; // Filtrar frecuencias altas

        const highpass = audioContext.createBiquadFilter();
        highpass.type = 'highpass';
        highpass.frequency.value = 200; // Filtrar frecuencias muy bajas

        // Crear control de volumen
        const gainNode = audioContext.createGain();
        gainNode.gain.value = rainVolume.value;

        // Conectar nodos
        source.connect(lowpass);
        lowpass.connect(highpass);
        highpass.connect(gainNode);
        gainNode.connect(audioContext.destination);

        // Guardar referencia para control posterior
        (source as any).gainNode = gainNode;
        (source as any).audioContext = audioContext;
        rainAudio = source as any;

        console.log('🔊 Sistema de audio de lluvia inicializado');
    } catch (error) {
        console.warn('No se pudo inicializar el audio de lluvia:', error);
        soundsEnabled.value = false;
    }
};

// Función para reproducir sonido de lluvia
const playRainSound = () => {
    if (!soundsEnabled.value || !rainAudio) return;

    try {
        if (
            rainAudio.audioContext &&
            rainAudio.audioContext.state === 'suspended'
        ) {
            rainAudio.audioContext.resume();
        }
        rainAudio.start();
        console.log('🎵 Sonido de lluvia iniciado');
    } catch (error) {
        console.warn('Error al reproducir sonido de lluvia:', error);
    }
};

// Función para detener sonido de lluvia
const stopRainSound = () => {
    if (!rainAudio) return;

    try {
        rainAudio.stop();
        rainAudio = null;
        console.log('🔇 Sonido de lluvia detenido');
    } catch (error) {
        console.warn('Error al detener sonido de lluvia:', error);
    }
};

// Función para reproducir sonido de trueno
const playThunderSound = () => {
    if (!soundsEnabled.value) return;

    try {
        const audioContext = new (window.AudioContext ||
            (window as any).webkitAudioContext)();

        // Generar sonido de trueno procedural
        const bufferSize = audioContext.sampleRate * 3; // 3 segundos
        const buffer = audioContext.createBuffer(
            1,
            bufferSize,
            audioContext.sampleRate,
        );
        const data = buffer.getChannelData(0);

        // Generar trueno con envolvente
        for (let i = 0; i < bufferSize; i++) {
            const t = i / audioContext.sampleRate;
            const envelope = Math.exp(-t * 2); // Decaimiento exponencial
            const noise = (Math.random() * 2 - 1) * envelope * 0.3;
            const rumble = Math.sin(t * 60 * Math.PI) * envelope * 0.2; // Frecuencia baja
            data[i] = noise + rumble;
        }

        const source = audioContext.createBufferSource();
        source.buffer = buffer;

        // Filtro para el trueno
        const lowpass = audioContext.createBiquadFilter();
        lowpass.type = 'lowpass';
        lowpass.frequency.value = 500;

        const gainNode = audioContext.createGain();
        gainNode.gain.value = rainVolume.value * 0.8;

        source.connect(lowpass);
        lowpass.connect(gainNode);
        gainNode.connect(audioContext.destination);

        source.start();
        console.log('⚡ Sonido de trueno reproducido');
    } catch (error) {
        console.warn('Error al reproducir trueno:', error);
    }
};

// Función para iniciar la lluvia
const startRain = () => {
    if (isRaining.value) return;

    isRaining.value = true;

    // Inicializar y reproducir sonidos
    initRainSounds();
    setTimeout(() => playRainSound(), 500); // Pequeño delay para mejor sincronización

    // Crear sistema de lluvia si no existe
    if (!rainParticleSystem) {
        createRainSystem();
    }

    // Agregar lluvia a la escena
    if (rainParticleSystem) {
        scene.add(rainParticleSystem);
    }

    // Crear efectos de charcos
    createPuddles();

    // Animar transición de iluminación
    animateLightingTransition(true);

    console.log('🌧️ Lluvia iniciada');
};

// Función para detener la lluvia
const stopRain = () => {
    if (!isRaining.value) return;

    isRaining.value = false;

    // Detener sonidos de lluvia
    stopRainSound();

    // Remover lluvia de la escena
    if (rainParticleSystem) {
        scene.remove(rainParticleSystem);
    }

    // Remover charcos
    removePuddles();

    // Restaurar iluminación
    animateLightingTransition(false);

    console.log('☀️ Lluvia detenida');
};

// Función para crear charcos de agua mejorados
const createPuddles = () => {
    const puddleCount = 20; // Más charcos para mayor realismo

    for (let i = 0; i < puddleCount; i++) {
        // Posición aleatoria en el terreno
        const x = (Math.random() - 0.5) * 40;
        const z = (Math.random() - 0.5) * 40;
        const size = Math.random() * 3 + 0.8; // Tamaños más variados

        // Geometría del charco con más segmentos para ondas
        const puddleGeometry = new THREE.PlaneGeometry(size, size, 16, 16);

        // Crear ondas sutiles en la superficie
        const positions = puddleGeometry.attributes.position;
        for (let j = 0; j < positions.count; j++) {
            const x = positions.getX(j);
            const z = positions.getZ(j);
            const wave = Math.sin(x * 2) * Math.cos(z * 2) * 0.02;
            positions.setY(j, wave);
        }
        positions.needsUpdate = true;
        puddleGeometry.computeVertexNormals();

        puddleGeometries.push(puddleGeometry);

        // Material reflectante mejorado para el agua
        const puddleMaterial = new THREE.MeshStandardMaterial({
            color: new THREE.Color(0x2c5aa0), // Color más natural del agua
            transparent: true,
            opacity: 0.8,
            roughness: 0.05, // Más reflectante
            metalness: 0.9,
            envMapIntensity: 1.5, // Mayor intensidad de reflexión
            side: THREE.DoubleSide,
        });

        // Agregar textura de ondas si está disponible
        if (typeof THREE.TextureLoader !== 'undefined') {
            // Crear textura procedural de ondas
            const canvas = document.createElement('canvas');
            canvas.width = 64;
            canvas.height = 64;
            const ctx = canvas.getContext('2d');

            if (ctx) {
                // Crear patrón de ondas
                const gradient = ctx.createRadialGradient(
                    32,
                    32,
                    0,
                    32,
                    32,
                    32,
                );
                gradient.addColorStop(0, 'rgba(255,255,255,0.3)');
                gradient.addColorStop(0.5, 'rgba(255,255,255,0.1)');
                gradient.addColorStop(1, 'rgba(255,255,255,0)');

                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, 64, 64);

                const texture = new THREE.CanvasTexture(canvas);
                texture.wrapS = THREE.RepeatWrapping;
                texture.wrapT = THREE.RepeatWrapping;
                texture.repeat.set(2, 2);

                puddleMaterial.normalMap = texture;
                puddleMaterial.normalScale = new THREE.Vector2(0.5, 0.5);
            }
        }

        puddleMaterials.push(puddleMaterial);

        // Crear mesh del charco
        const puddle = new THREE.Mesh(puddleGeometry, puddleMaterial);
        puddle.rotation.x = -Math.PI / 2;
        puddle.position.set(x, 0.005, z); // Ligeramente más bajo
        puddle.receiveShadow = true;
        puddle.castShadow = false;

        // Agregar datos para animación
        puddle.userData = {
            originalY: 0.005,
            wavePhase: Math.random() * Math.PI * 2,
            waveSpeed: 0.5 + Math.random() * 0.5,
            size: size,
        };

        puddles.push(puddle);
        scene.add(puddle);
    }

    // Iniciar animación de ondas en charcos
    animatePuddles();
};

// Función para animar ondas en los charcos
const animatePuddles = () => {
    if (!isRaining.value || puddles.length === 0) return;

    const time = Date.now() * 0.001;

    puddles.forEach((puddle, index) => {
        const userData = puddle.userData;

        // Animar ondas sutiles
        const waveOffset =
            Math.sin(time * userData.waveSpeed + userData.wavePhase) * 0.003;
        puddle.position.y = userData.originalY + waveOffset;

        // Animar opacidad para simular ondas
        if (puddle.material instanceof THREE.MeshStandardMaterial) {
            const baseOpacity = 0.8;
            const opacityVariation = Math.sin(time * 2 + index) * 0.1;
            puddle.material.opacity = baseOpacity + opacityVariation;
        }

        // Rotar ligeramente para simular movimiento del agua
        puddle.rotation.z += 0.001;
    });

    // Continuar animación si sigue lloviendo
    if (isRaining.value) {
        requestAnimationFrame(animatePuddles);
    }
};

// Función para remover charcos
const removePuddles = () => {
    puddles.forEach((puddle) => {
        scene.remove(puddle);
    });

    // Limpiar arrays
    puddles.length = 0;
    puddleGeometries.forEach((geo) => geo.dispose());
    puddleGeometries.length = 0;
    puddleMaterials.forEach((mat) => mat.dispose());
    puddleMaterials.length = 0;
};

// Función para animar transición de iluminación mejorada
const animateLightingTransition = (toRainy: boolean) => {
    if (!directionalLight || !ambientLight) return;

    const duration = 3000; // 3 segundos para transición más suave
    const startTime = Date.now();

    const startDirectional = directionalLight.intensity;
    const startAmbient = ambientLight.intensity;
    const startBgColor = scene.background?.clone() || new THREE.Color(0x87ceeb);

    // Colores más dramáticos para el clima lluvioso
    const targetDirectional = toRainy
        ? originalLightIntensity * 0.2
        : originalLightIntensity;
    const targetAmbient = toRainy
        ? originalAmbientIntensity * 0.4
        : originalAmbientIntensity;
    const targetBgColor = toRainy
        ? new THREE.Color(0x2c3e50)
        : new THREE.Color(0x87ceeb);

    // Agregar niebla para mayor realismo
    if (toRainy) {
        scene.fog = new THREE.Fog(0x2c3e50, 10, 100);
    } else {
        scene.fog = null;
    }

    const animate = () => {
        const elapsed = Date.now() - startTime;
        const progress = Math.min(elapsed / duration, 1);

        // Interpolación suave con curva más natural
        const easeProgress = 1 - Math.pow(1 - progress, 4);

        // Animar intensidad de luces con variaciones dinámicas
        const lightFlicker = toRainy ? Math.sin(Date.now() * 0.01) * 0.05 : 0;
        directionalLight.intensity =
            startDirectional +
            (targetDirectional - startDirectional) * easeProgress +
            lightFlicker;
        ambientLight.intensity =
            startAmbient +
            (targetAmbient - startAmbient) * easeProgress +
            lightFlicker * 0.5;

        // Animar color de fondo con variaciones sutiles
        if (scene.background instanceof THREE.Color) {
            const currentColor = startBgColor
                .clone()
                .lerp(targetBgColor, easeProgress);

            // Agregar variación sutil de color durante la lluvia
            if (toRainy && progress > 0.5) {
                const colorVariation = Math.sin(Date.now() * 0.005) * 0.02;
                currentColor.r += colorVariation;
                currentColor.g += colorVariation;
                currentColor.b += colorVariation;
            }

            scene.background.copy(currentColor);
        }

        // Efecto de relámpago ocasional durante la lluvia
        if (toRainy && progress > 0.8 && Math.random() < 0.002) {
            createLightningEffect();
        }

        if (progress < 1) {
            requestAnimationFrame(animate);
        } else if (toRainy) {
            // Iniciar efectos dinámicos continuos durante la lluvia
            startDynamicWeatherEffects();
        }
    };

    animate();
};

// Función para crear efectos de relámpago
const createLightningEffect = () => {
    if (!directionalLight || !ambientLight) return;

    const originalDirectional = directionalLight.intensity;
    const originalAmbient = ambientLight.intensity;
    const originalBg = scene.background?.clone();

    // Flash de relámpago
    directionalLight.intensity = originalLightIntensity * 2;
    ambientLight.intensity = originalAmbientIntensity * 1.5;
    if (scene.background instanceof THREE.Color) {
        scene.background.setHex(0xffffff);
    }

    // Reproducir sonido de trueno con delay realista
    setTimeout(
        () => {
            playThunderSound();
        },
        Math.random() * 2000 + 500,
    ); // Delay de 0.5 a 2.5 segundos

    // Restaurar después de un breve momento
    setTimeout(() => {
        directionalLight.intensity = originalDirectional;
        ambientLight.intensity = originalAmbient;
        if (originalBg && scene.background instanceof THREE.Color) {
            scene.background.copy(originalBg);
        }
    }, 100);

    console.log('⚡ Relámpago!');
};

// Función para efectos dinámicos continuos del clima
const startDynamicWeatherEffects = () => {
    if (!isRaining.value) return;

    const updateWeatherEffects = () => {
        if (!isRaining.value || !directionalLight || !ambientLight) return;

        const time = Date.now() * 0.001;

        // Variaciones sutiles de iluminación para simular nubes moviéndose
        const cloudEffect =
            Math.sin(time * 0.3) * 0.1 + Math.cos(time * 0.2) * 0.05;
        directionalLight.intensity = originalLightIntensity * 0.2 + cloudEffect;
        ambientLight.intensity =
            originalAmbientIntensity * 0.4 + cloudEffect * 0.5;

        // Variación del color de fondo
        if (scene.background instanceof THREE.Color) {
            const baseColor = new THREE.Color(0x2c3e50);
            const variation = Math.sin(time * 0.1) * 0.1;
            baseColor.r += variation;
            baseColor.g += variation;
            baseColor.b += variation;
            scene.background.copy(baseColor);
        }

        // Continuar efectos si sigue lloviendo
        if (isRaining.value) {
            setTimeout(updateWeatherEffects, 100);
        }
    };

    updateWeatherEffects();
};

// Función para activar/desactivar el sistema de aspersores
const toggleSprinklerSystem = () => {
    sprinklerSystemActive.value = !sprinklerSystemActive.value;

    if (sprinklerSystemActive.value) {
        console.log('💧 Sistema de riego activado');
        startSprinklerEffects();
    } else {
        console.log('💧 Sistema de riego desactivado');
        stopSprinklerEffects();
    }
};

// Función para iniciar efectos de aspersores
const startSprinklerEffects = () => {
    if (placedSprinklers.length === 0) {
        console.warn('⚠️ No hay aspersores colocados');
        return;
    }

    // Crear efectos de agua para cada aspersor
    placedSprinklers.forEach((sprinkler, index) => {
        createWaterEffect(sprinkler.position.x, sprinkler.position.z, index);
    });
};

// Función para detener efectos de aspersores
const stopSprinklerEffects = () => {
    // Remover todos los efectos de agua
    sprinklerWaterEffects.forEach((effect) => {
        if (effect.parent) {
            effect.parent.remove(effect);
        }
    });
    sprinklerWaterEffects.length = 0;

    // Detener animaciones
    sprinklerAnimations.forEach((animation) => {
        if (animation.stop) {
            animation.stop();
        }
    });
    sprinklerAnimations.length = 0;
};

// Función para crear efectos de agua de un aspersor
const createWaterEffect = (x: number, z: number, sprinklerIndex: number) => {
    const particleCount = 300; // Aumentamos el número de partículas para mejor efecto
    const geometry = new THREE.BufferGeometry();
    const positions = new Float32Array(particleCount * 3);
    const velocities = new Float32Array(particleCount * 3);

    // Inicializar partículas
    for (let i = 0; i < particleCount; i++) {
        const i3 = i * 3;

        // Posición inicial (en el aspersor)
        positions[i3] = x;
        positions[i3 + 1] = 0.5; // Altura del aspersor
        positions[i3 + 2] = z;

        // Velocidades aleatorias en patrón circular más realista
        const angle = (Math.PI * 2 * i) / particleCount + Math.random() * 0.5;
        const speed = 0.03 + Math.random() * 0.04; // Velocidad más variada
        const heightVariation = 0.06 + Math.random() * 0.05; // Más variación en altura

        velocities[i3] = Math.cos(angle) * speed;
        velocities[i3 + 1] = heightVariation; // Velocidad hacia arriba
        velocities[i3 + 2] = Math.sin(angle) * speed;
    }

    geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    geometry.setAttribute('velocity', new THREE.BufferAttribute(velocities, 3));

    const material = new THREE.PointsMaterial({
        color: 0x4fc3f7, // Azul agua
        size: 0.08, // Partículas más grandes
        transparent: true,
        opacity: 0.9,
        blending: THREE.AdditiveBlending,
        sizeAttenuation: true, // Las partículas se ven más pequeñas con la distancia
    });

    const waterParticles = new THREE.Points(geometry, material);
    scene.add(waterParticles);
    sprinklerWaterEffects.push(waterParticles);

    // Animación de las partículas mejorada
    const animateWater = () => {
        if (!sprinklerSystemActive.value) return;

        const positions = waterParticles.geometry.attributes.position
            .array as Float32Array;
        const velocities = waterParticles.geometry.attributes.velocity
            .array as Float32Array;

        for (let i = 0; i < particleCount; i++) {
            const i3 = i * 3;

            // Actualizar posiciones
            positions[i3] += velocities[i3];
            positions[i3 + 1] += velocities[i3 + 1];
            positions[i3 + 2] += velocities[i3 + 2];

            // Aplicar gravedad más realista
            velocities[i3 + 1] -= 0.003;

            // Añadir resistencia del aire
            velocities[i3] *= 0.998;
            velocities[i3 + 2] *= 0.998;

            // Reiniciar partícula si toca el suelo o se aleja mucho
            if (
                positions[i3 + 1] <= 0 ||
                Math.abs(positions[i3] - x) > 4 ||
                Math.abs(positions[i3 + 2] - z) > 4
            ) {
                // Reiniciar con pequeña variación en la posición inicial
                positions[i3] = x + (Math.random() - 0.5) * 0.3;
                positions[i3 + 1] = 0.5 + Math.random() * 0.2;
                positions[i3 + 2] = z + (Math.random() - 0.5) * 0.3;

                // Nueva velocidad aleatoria
                const angle = Math.random() * Math.PI * 2;
                const speed = 0.03 + Math.random() * 0.04;
                const heightVariation = 0.06 + Math.random() * 0.05;

                velocities[i3] = Math.cos(angle) * speed;
                velocities[i3 + 1] = heightVariation;
                velocities[i3 + 2] = Math.sin(angle) * speed;
            }
        }

        waterParticles.geometry.attributes.position.needsUpdate = true;

        if (sprinklerSystemActive.value) {
            requestAnimationFrame(animateWater);
        }
    };

    animateWater();

    // Guardar referencia para poder detener la animación
    const animationControl = {
        stop: () => {
            // La animación se detiene automáticamente cuando sprinklerSystemActive es false
        },
    };

    sprinklerAnimations.push(animationControl);
};
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap');

.font-fredoka {
    font-family: 'Fredoka', sans-serif;
}

.cursor-tractor {
    cursor:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><text y="24" font-size="24">🚜</text></svg>'),
        auto !important;
}

.cursor-sign {
    cursor:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><text y="24" font-size="24">🪧</text></svg>'),
        auto !important;
}

.cursor-potato {
    cursor:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><text y="24" font-size="24">🥔</text></svg>'),
        auto !important;
}

.cursor-alfalfa {
    cursor:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><text y="24" font-size="24">🍀</text></svg>'),
        auto !important;
}

.cursor-rain {
    cursor:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><text y="24" font-size="24">🌧️</text></svg>'),
        auto !important;
}

.cursor-sprinkler {
    cursor:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><text y="24" font-size="24">💧</text></svg>'),
        auto !important;
}

.cursor-harvest {
    cursor:
        url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><text y="24" font-size="24">🌾</text></svg>'),
        auto !important;
}
</style>
