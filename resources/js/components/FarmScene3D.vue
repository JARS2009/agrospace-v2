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
        <div ref="sceneContainer" class="h-full w-full"></div>
    </div>
</template>

<script setup lang="ts">
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { onMounted, onUnmounted, ref } from 'vue';

const sceneContainer = ref<HTMLElement>();
let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let renderer: THREE.WebGLRenderer;
let controls: OrbitControls;
let animationId: number;

let gltfLoader: GLTFLoader;
let textureLoader: THREE.TextureLoader;

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

// Función para crear el terreno realista
const createTerrain = () => {
    setLoadingMessage('Cargando texturas del terreno...');

    // Cargar texturas PBR de Ground068
    const colorTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_Color.jpg',
        updateLoadingProgress,
    );
    const normalTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_NormalGL.jpg',
        updateLoadingProgress,
    );
    const roughnessTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_Roughness.jpg',
        updateLoadingProgress,
    );
    const aoTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_AmbientOcclusion.jpg',
        updateLoadingProgress,
    );
    const displacementTexture = textureLoader.load(
        '/textures/Ground068_1K-JPG_Displacement.jpg',
        updateLoadingProgress,
    );

    // Configurar repetición de texturas para que se vea natural en el terreno más grande
    const textureRepeat = 8;
    [
        colorTexture,
        normalTexture,
        roughnessTexture,
        aoTexture,
        displacementTexture,
    ].forEach((texture) => {
        texture.wrapS = THREE.RepeatWrapping;
        texture.wrapT = THREE.RepeatWrapping;
        texture.repeat.set(textureRepeat, textureRepeat);
    });

    // Crear el plano base del terreno con más subdivisiones para el displacement
    const terrainGeometry = new THREE.PlaneGeometry(50, 50, 256, 256);

    // Material PBR realista del terreno
    const terrainMaterial = new THREE.MeshStandardMaterial({
        map: colorTexture, // Color base
        normalMap: normalTexture, // Detalles de superficie
        roughnessMap: roughnessTexture, // Rugosidad
        aoMap: aoTexture, // Ambient Occlusion
        displacementMap: displacementTexture, // Desplazamiento real
        displacementScale: 0.05, // Escala del desplazamiento
        aoMapIntensity: 1.0, // Intensidad del AO
    });

    const terrain = new THREE.Mesh(terrainGeometry, terrainMaterial);
    terrain.rotation.x = -Math.PI / 2; // Rotar para que sea horizontal
    terrain.position.y = 0;
    terrain.receiveShadow = true; // Para recibir sombras
    scene.add(terrain);
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

// Función para crear árboles realistas usando GLTF
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

    // Inicializar GLTFLoader y TextureLoader
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
    // Calcular total de assets a cargar: 5 texturas + 1 casa + 1 árbol
    totalAssets = 7;
    loadedAssets = 0;

    setLoadingMessage('Iniciando carga de elementos...');

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

    window.removeEventListener('resize', handleResize);

    if (renderer) {
        renderer.dispose();
    }

    if (controls) {
        controls.dispose();
    }
});
</script>
