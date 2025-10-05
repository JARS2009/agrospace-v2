<template>
    <div class="relative h-full w-full overflow-hidden rounded-xl">
        <!-- Loading Screen -->
        <div
            v-if="isLoading"
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
    signText?: string;
}

const props = withDefaults(defineProps<Props>(), {
    plowMode: false,
    signMode: false,
    signText: '',
});

// Emits para comunicar cambios al componente padre
const emit = defineEmits<{
    plowModeChanged: [value: boolean];
    signPlaced: [];
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

// Loading state variables
const isLoading = ref(true);
const loadingMessage = ref('Inicializando...');
const loadingProgress = ref(0);
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
    if (event.button !== 0 || (!props.plowMode && !props.signMode)) return;

    event.preventDefault();

    if (!sceneContainer.value) return;

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

// Función para manejar movimiento del mouse (previsualización)
const onMouseMove = (event: MouseEvent) => {
    if ((!props.plowMode && !props.signMode) || !sceneContainer.value) return;

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
        house.scale.set(0.1, 0.1, 0.1);
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
    const ambientLight = new THREE.AmbientLight(0x404040, 0.4);
    scene.add(ambientLight);

    // Luz direccional principal (sol)
    const directionalLight = new THREE.DirectionalLight(0xffffff, 1.2);
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
    totalAssets = 12; // 10 texturas + 1 casa + 1 árbol
    loadedAssets = 0;

    setLoadingMessage('Iniciando carga de elementos...');

    createTerrainMaterials();
    createTerrain();
    await createRealisticHouses();
    await createRealisticTrees();

    // Iniciar animación
    animate();
};

// Función de animación
const animate = () => {
    animationId = requestAnimationFrame(animate);

    // Actualizar controles
    controls.update();

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

onMounted(async () => {
    await initScene();
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
</style>
