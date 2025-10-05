<template>
    <div class="relative h-full w-full overflow-hidden rounded-xl">
        <!-- Contenedor de la escena 3D -->
        <div ref="sceneContainer" class="h-full w-full bg-gradient-to-br from-amber-50 to-orange-100"></div>
        
        <!-- Información de las papas cosechadas -->
        <div class="absolute top-4 left-4 z-10 bg-white/90 backdrop-blur-sm rounded-lg p-4 shadow-lg">
            <h3 class="font-fredoka text-lg font-bold text-amber-800 mb-2 flex items-center">
                <span class="mr-2">🥔</span>
                Papas Cosechadas
            </h3>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Cantidad:</span>
                    <span class="font-semibold text-amber-700">{{ potatoCount }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Calidad:</span>
                    <span class="font-semibold text-green-600">{{ quality }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">XP Ganada:</span>
                    <span class="font-semibold text-blue-600">+{{ xpGained }}</span>
                </div>
            </div>
        </div>

        <!-- Botón para continuar -->
        <div class="absolute bottom-4 right-4 z-10">
            <button
                @click="$emit('continue')"
                class="font-fredoka bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-lg font-semibold shadow-lg transition-all duration-300 hover:scale-105 flex items-center space-x-2"
            >
                <span>✅</span>
                <span>Continuar</span>
            </button>
        </div>

        <!-- Efectos de partículas de celebración -->
        <div v-if="showCelebration" class="absolute inset-0 pointer-events-none z-20">
            <div class="celebration-particles"></div>
        </div>
    </div>
</template>

<script setup lang="ts">
import * as THREE from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { GLTFLoader } from 'three/examples/jsm/loaders/GLTFLoader.js';
import { onMounted, onUnmounted, ref } from 'vue';

interface Props {
    potatoCount?: number;
    quality?: string;
    xpGained?: number;
}

const props = withDefaults(defineProps<Props>(), {
    potatoCount: 3,
    quality: 'Excelente',
    xpGained: 50,
});

const emit = defineEmits<{
    continue: [];
}>();

const sceneContainer = ref<HTMLElement>();
const showCelebration = ref(true);

let scene: THREE.Scene;
let camera: THREE.PerspectiveCamera;
let renderer: THREE.WebGLRenderer;
let controls: OrbitControls;
let animationId: number;
let gltfLoader: GLTFLoader;

// Array para almacenar las papas 3D
const potatoes: THREE.Group[] = [];
let potatoModel: THREE.Group | null = null;

// Variables para animación
let animationTime = 0;

const initThreeJS = async () => {
    if (!sceneContainer.value) return;

    // Crear escena
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf0f8ff);

    // Crear cámara
    camera = new THREE.PerspectiveCamera(
        75,
        sceneContainer.value.clientWidth / sceneContainer.value.clientHeight,
        0.1,
        1000
    );
    camera.position.set(0, 5, 8);

    // Crear renderer
    renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(sceneContainer.value.clientWidth, sceneContainer.value.clientHeight);
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    sceneContainer.value.appendChild(renderer.domElement);

    // Crear controles
    controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.enableZoom = true;
    controls.enablePan = false;
    controls.maxPolarAngle = Math.PI / 2;

    // Configurar iluminación
    setupLighting();

    // Cargar modelo de papa
    await loadPotatoModel();

    // Crear las papas cosechadas
    createHarvestedPotatoes();

    // Crear superficie base
    createBaseSurface();

    // Iniciar animación
    animate();

    // Ocultar celebración después de 3 segundos
    setTimeout(() => {
        showCelebration.value = false;
    }, 3000);
};

const setupLighting = () => {
    // Luz ambiental suave
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambientLight);

    // Luz direccional principal
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
    directionalLight.position.set(10, 10, 5);
    directionalLight.castShadow = true;
    directionalLight.shadow.mapSize.width = 2048;
    directionalLight.shadow.mapSize.height = 2048;
    directionalLight.shadow.camera.near = 0.5;
    directionalLight.shadow.camera.far = 50;
    directionalLight.shadow.camera.left = -10;
    directionalLight.shadow.camera.right = 10;
    directionalLight.shadow.camera.top = 10;
    directionalLight.shadow.camera.bottom = -10;
    scene.add(directionalLight);

    // Luz de relleno
    const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
    fillLight.position.set(-5, 5, -5);
    scene.add(fillLight);
};

const loadPotatoModel = async () => {
    gltfLoader = new GLTFLoader();
    
    try {
        const gltf = await gltfLoader.loadAsync('/models/potato/scene.gltf');
        potatoModel = gltf.scene.clone();
        
        // Escalar el modelo
        potatoModel.scale.set(0.5, 0.5, 0.5);
        
        // Habilitar sombras
        potatoModel.traverse((child) => {
            if (child instanceof THREE.Mesh) {
                child.castShadow = true;
                child.receiveShadow = true;
                
                // Mejorar el material si es necesario
                if (child.material) {
                    child.material.roughness = 0.8;
                    child.material.metalness = 0.1;
                }
            }
        });
        
        console.log('Modelo de papa cargado exitosamente');
    } catch (error) {
        console.error('Error cargando modelo de papa:', error);
        // Crear papa básica si no se puede cargar el modelo
        createBasicPotato();
    }
};

const createBasicPotato = () => {
    // Crear una papa básica usando geometría primitiva
    const potatoGeometry = new THREE.SphereGeometry(0.3, 16, 12);
    potatoGeometry.scale(1, 0.8, 1.2); // Hacer más ovalada
    
    const potatoMaterial = new THREE.MeshStandardMaterial({
        color: 0xd2b48c,
        roughness: 0.9,
        metalness: 0.1,
    });
    
    potatoModel = new THREE.Group();
    const potatoMesh = new THREE.Mesh(potatoGeometry, potatoMaterial);
    potatoMesh.castShadow = true;
    potatoMesh.receiveShadow = true;
    potatoModel.add(potatoMesh);
};

const createHarvestedPotatoes = () => {
    if (!potatoModel) return;

    const positions = [
        { x: -2, z: 0 },
        { x: 0, z: 0 },
        { x: 2, z: 0 },
    ];

    for (let i = 0; i < props.potatoCount && i < positions.length; i++) {
        const potato = potatoModel.clone();
        const pos = positions[i];
        
        // Posición inicial (caerán desde arriba)
        potato.position.set(pos.x, 5 + i * 0.5, pos.z);
        
        // Rotación aleatoria
        potato.rotation.set(
            Math.random() * Math.PI,
            Math.random() * Math.PI,
            Math.random() * Math.PI
        );
        
        // Escala ligeramente aleatoria
        const scale = 0.8 + Math.random() * 0.4;
        potato.scale.multiplyScalar(scale);
        
        scene.add(potato);
        potatoes.push(potato);
    }
};

const createBaseSurface = () => {
    // Crear una superficie base para que las papas caigan
    const surfaceGeometry = new THREE.PlaneGeometry(10, 10);
    const surfaceMaterial = new THREE.MeshStandardMaterial({
        color: 0x8fbc8f,
        roughness: 0.8,
        metalness: 0.1,
    });
    
    const surface = new THREE.Mesh(surfaceGeometry, surfaceMaterial);
    surface.rotation.x = -Math.PI / 2;
    surface.position.y = 0;
    surface.receiveShadow = true;
    scene.add(surface);
};

const animate = () => {
    animationId = requestAnimationFrame(animate);
    
    animationTime += 0.016; // ~60fps
    
    // Animar las papas cayendo
    potatoes.forEach((potato, index) => {
        if (potato.position.y > 0.5) {
            // Caída con gravedad
            potato.position.y -= 0.1;
            
            // Rotación durante la caída
            potato.rotation.x += 0.05;
            potato.rotation.z += 0.03;
        } else {
            // Rebote suave al tocar el suelo
            if (potato.position.y < 0.5 && potato.position.y > 0.3) {
                potato.position.y = 0.5;
            }
            
            // Animación de flotación suave
            potato.position.y = 0.5 + Math.sin(animationTime * 2 + index) * 0.1;
            
            // Rotación lenta
            potato.rotation.y += 0.01;
        }
    });
    
    controls.update();
    renderer.render(scene, camera);
};

const handleResize = () => {
    if (!sceneContainer.value) return;
    
    camera.aspect = sceneContainer.value.clientWidth / sceneContainer.value.clientHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(sceneContainer.value.clientWidth, sceneContainer.value.clientHeight);
};

onMounted(() => {
    initThreeJS();
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    if (animationId) {
        cancelAnimationFrame(animationId);
    }
    
    if (renderer) {
        renderer.dispose();
    }
    
    window.removeEventListener('resize', handleResize);
});
</script>

<style scoped>
.celebration-particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255, 215, 0, 0.3) 0%, transparent 70%);
    animation: celebrate 3s ease-out;
}

@keyframes celebrate {
    0% {
        opacity: 0;
        transform: scale(0.5);
    }
    50% {
        opacity: 1;
        transform: scale(1.1);
    }
    100% {
        opacity: 0;
        transform: scale(1);
    }
}

.font-fredoka {
    font-family: 'Fredoka One', cursive;
}
</style>