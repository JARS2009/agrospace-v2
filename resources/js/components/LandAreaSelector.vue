<template>
    <div class="land-area-selector">
        <!-- Mapa -->
        <div
            ref="mapContainer"
            class="h-96 w-full rounded-xl border border-green-300 shadow-lg"
            style="min-height: 400px"
        ></div>

        <!-- Información del área calculada -->
        <div
            v-if="showControls && areaData.size > 0"
            class="mt-4 rounded-xl border border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-4 shadow-sm"
        >
            <h4
                class="font-fredoka mb-2 flex items-center gap-2 font-semibold text-green-800"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                    ></path>
                </svg>
                Información del área
            </h4>
            <p class="font-fredoka text-sm text-green-700">
                <strong>Área calculada:</strong>
                {{ areaData.size.toFixed(2) }} hectáreas
            </p>
            <p class="font-fredoka mt-1 text-sm text-green-700">
                <strong>Puntos definidos:</strong>
                {{ areaData.coordinates.length }}
            </p>
        </div>

        <!-- Coordenadas detalladas -->
        <div
            v-if="showControls && areaData.coordinates.length > 0"
            class="mt-4 rounded-xl border border-blue-200 bg-gradient-to-r from-blue-50 to-sky-50 p-4 shadow-sm"
        >
            <h4
                class="font-fredoka mb-2 flex items-center gap-2 font-semibold text-blue-800"
            >
                <svg
                    class="h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
                    ></path>
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                    ></path>
                </svg>
                Coordenadas
            </h4>
            <div class="max-h-32 overflow-y-auto">
                <div
                    v-for="(coord, index) in areaData.coordinates"
                    :key="index"
                    class="font-fredoka text-xs text-blue-700"
                >
                    Punto {{ index + 1 }}: {{ coord.lat.toFixed(6) }},
                    {{ coord.lng.toFixed(6) }}
                </div>
            </div>
        </div>

        <!-- Campos ocultos para el formulario -->
        <input
            v-if="showControls"
            type="hidden"
            name="land_area_coordinates"
            :value="JSON.stringify(areaData.coordinates)"
        />
        <input
            v-if="showControls"
            type="hidden"
            name="land_area_size"
            :value="areaData.size"
        />
        <input
            v-if="showControls"
            type="hidden"
            name="land_area_name"
            :value="areaData.name"
        />
        <input
            v-if="showControls"
            type="hidden"
            name="land_area_description"
            :value="areaData.description"
        />
    </div>
</template>

<script setup lang="ts">
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { nextTick, onMounted, reactive, ref, watch } from 'vue';
// @ts-ignore
import 'leaflet-geometryutil';

// Props del componente
interface Props {
    readonly?: boolean;
    showControls?: boolean;
    initialCoordinates?: Array<{ lat: number; lng: number }>;
    initialSize?: number;
    initialName?: string;
    initialDescription?: string;
}

const props = withDefaults(defineProps<Props>(), {
    readonly: false,
    showControls: true,
    initialCoordinates: () => [],
    initialSize: 0,
    initialName: '',
    initialDescription: '',
});

// Fix para los iconos de Leaflet
delete (L.Icon.Default.prototype as any)._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon-2x.png',
    iconUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
    shadowUrl:
        'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
});

const mapContainer = ref<HTMLElement>();
let map: L.Map | null = null;
let polygon: L.Polygon | null = null;
let markers: L.Marker[] = [];

const areaData = reactive({
    coordinates: [...props.initialCoordinates] as Array<{
        lat: number;
        lng: number;
    }>,
    size: props.initialSize,
    name: props.initialName,
    description: props.initialDescription,
});

const initMap = async () => {
    await nextTick();

    if (!mapContainer.value) {
        console.error('Map container not found');
        return;
    }

    try {
        // Agregar capa satelital como opción
        const satelliteLayer = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                attribution: 'Tiles © Esri',
            },
        );

        // Inicializar el mapa centrado en Cajamarca, Perú con vista satelital por defecto
        map = L.map(mapContainer.value).setView([-7.156801, -78.516861], 15);

        // Agregar la capa satelital por defecto
        satelliteLayer.addTo(map);

        // Crear capa de OpenStreetMap para el control de capas
        const osmLayer = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                attribution: '© OpenStreetMap contributors',
            },
        );

        const baseMaps = {
            Satélite: satelliteLayer,
            Mapa: osmLayer,
        };

        L.control.layers(baseMaps).addTo(map);

        // Evento de clic en el mapa (solo si no es readonly)
        if (!props.readonly) {
            map.on('click', onMapClick);
        }

        console.log('Map initialized successfully');
    } catch (error) {
        console.error('Error initializing map:', error);
    }
};

const onMapClick = (e: L.LeafletMouseEvent) => {
    if (props.readonly) return;

    const { lat, lng } = e.latlng;

    // Agregar marcador draggable
    const marker = L.marker([lat, lng], {
        draggable: !props.readonly,
        icon: L.divIcon({
            className: 'custom-marker',
            html: `<div class="w-4 h-4 bg-blue-500 border-2 border-white rounded-full shadow-lg"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
        }),
    }).addTo(map!);

    // Evento para cuando se arrastra el marcador
    if (!props.readonly) {
        marker.on('dragend', () => {
            updateCoordinates();
            updatePolygon();
            // Calcular área automáticamente después de actualizar
            if (areaData.coordinates.length >= 3) {
                calculateArea();
            }
        });
    }

    markers.push(marker);
    areaData.coordinates.push({ lat, lng });

    // Actualizar polígono
    updatePolygon();

    // Calcular área automáticamente si tenemos al menos 3 puntos
    if (areaData.coordinates.length >= 3) {
        calculateArea();
    }
};

const updateCoordinates = () => {
    areaData.coordinates = markers.map((marker) => {
        const latlng = marker.getLatLng();
        return { lat: latlng.lat, lng: latlng.lng };
    });
};

const addMarker = (latlng: L.LatLng) => {
    const marker = L.marker(latlng, {
        draggable: !props.readonly,
        icon: L.divIcon({
            className: 'custom-marker',
            html: `<div class="w-4 h-4 bg-blue-500 border-2 border-white rounded-full shadow-lg"></div>`,
            iconSize: [16, 16],
            iconAnchor: [8, 8],
        }),
    }).addTo(map!);

    if (!props.readonly) {
        marker.on('dragend', () => {
            updateCoordinates();
            updatePolygon();
            // Calcular área automáticamente después de actualizar
            if (areaData.coordinates.length >= 3) {
                calculateArea();
            }
        });
    }

    markers.push(marker);
    areaData.coordinates.push({ lat: latlng.lat, lng: latlng.lng });
    updatePolygon();

    // Calcular área automáticamente si tenemos al menos 3 puntos
    if (areaData.coordinates.length >= 3) {
        calculateArea();
    }
};

const updatePolygon = () => {
    if (!map) return;

    // Remover polígono anterior
    if (polygon) {
        map.removeLayer(polygon);
        polygon = null;
    }

    // Crear nuevo polígono si hay al menos 3 puntos
    if (areaData.coordinates.length >= 3) {
        const latlngs = areaData.coordinates.map((coord) => [
            coord.lat,
            coord.lng,
        ]);
        polygon = L.polygon(latlngs, {
            color: '#3b82f6', // Azul
            fillColor: '#3b82f6', // Azul
            fillOpacity: 0.3,
            weight: 3,
            opacity: 0.8,
        }).addTo(map);
    }
};

const calculateArea = () => {
    if (!polygon || areaData.coordinates.length < 3) {
        areaData.size = 0;
        return;
    }

    try {
        // Calcular área usando la fórmula de Shoelace para coordenadas geográficas
        const coordinates = areaData.coordinates;
        if (coordinates.length < 3) {
            areaData.size = 0;
            return;
        }

        // Convertir coordenadas a metros usando proyección aproximada
        const earthRadius = 6371000; // Radio de la Tierra en metros
        const centerLat =
            coordinates.reduce((sum, coord) => sum + coord.lat, 0) /
            coordinates.length;
        const centerLatRad = (centerLat * Math.PI) / 180;

        // Convertir coordenadas geográficas a coordenadas planas en metros
        const projectedCoords = coordinates.map((coord) => {
            const latRad = (coord.lat * Math.PI) / 180;
            const lngRad = (coord.lng * Math.PI) / 180;
            const centerLngRad = (coordinates[0].lng * Math.PI) / 180;

            const x =
                earthRadius * (lngRad - centerLngRad) * Math.cos(centerLatRad);
            const y = earthRadius * (latRad - centerLatRad);

            return { x, y };
        });

        // Aplicar fórmula de Shoelace
        let area = 0;
        for (let i = 0; i < projectedCoords.length; i++) {
            const j = (i + 1) % projectedCoords.length;
            area += projectedCoords[i].x * projectedCoords[j].y;
            area -= projectedCoords[j].x * projectedCoords[i].y;
        }
        area = Math.abs(area) / 2;

        // Convertir de metros cuadrados a hectáreas
        areaData.size = Math.round((area / 10000) * 100) / 100;
        console.log('Area calculated:', areaData.size, 'hectares'); // Debug
    } catch (error) {
        console.error('Error calculating area:', error);
        areaData.size = 0;
    }
};

const clearArea = () => {
    if (!map) return;

    // Limpiar coordenadas
    areaData.coordinates = [];
    areaData.size = 0;

    // Remover marcadores
    markers.forEach((marker) => map!.removeLayer(marker));
    markers = [];

    // Remover polígono
    if (polygon) {
        map.removeLayer(polygon);
        polygon = null;
    }
};

onMounted(async () => {
    console.log('=== LAND AREA SELECTOR MOUNTED ===');
    console.log('Props received:', props);
    console.log('Initial coordinates prop:', props.initialCoordinates);
    console.log('Readonly mode:', props.readonly);

    await initMap();
    console.log('Map initialized successfully');

    // Fetch user's land area coordinates from API
    let coordinatesToUse = props.initialCoordinates || [];
    
    try {
        if (!props.initialCoordinates || props.initialCoordinates.length === 0) {
            console.log('Fetching user land area data from API...');
            const response = await fetch('/api/user/land-area', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin'
            });
            
            if (response.ok) {
                const userData = await response.json();
                console.log('User land area data received:', userData);
                
                if (userData.land_area_coordinates && userData.land_area_coordinates.length > 0) {
                    coordinatesToUse = userData.land_area_coordinates;
                    // Also update other area data
                    areaData.size = userData.land_area_size || 0;
                    areaData.name = userData.land_area_name || '';
                    areaData.description = userData.land_area_description || '';
                }
            } else {
                console.warn('Failed to fetch user land area data:', response.status);
            }
        }
    } catch (error) {
        console.error('Error fetching user land area data:', error);
    }

    console.log('Coordinates to use:', coordinatesToUse);

    // Si hay coordenadas iniciales, crear marcadores y polígono
    if (coordinatesToUse && coordinatesToUse.length > 0) {
        console.log('Processing coordinates:', coordinatesToUse);
        console.log(
            'Number of coordinates to process:',
            coordinatesToUse.length,
        );

        // Primero actualizar areaData con las coordenadas iniciales
        areaData.coordinates = [...coordinatesToUse];
        console.log('Updated areaData.coordinates:', areaData.coordinates);

        coordinatesToUse.forEach((coord, index) => {
            console.log(`Creating marker ${index} at:`, coord);

            if (!coord.lat || !coord.lng) {
                console.error(`Invalid coordinate at index ${index}:`, coord);
                return;
            }

            const marker = L.marker([coord.lat, coord.lng], {
                draggable: !props.readonly,
                icon: L.divIcon({
                    className: 'custom-marker',
                    html: `<div class="w-4 h-4 bg-blue-500 border-2 border-white rounded-full shadow-lg"></div>`,
                    iconSize: [16, 16],
                    iconAnchor: [8, 8],
                }),
            }).addTo(map!);

            console.log(
                `Marker ${index} added to map at lat: ${coord.lat}, lng: ${coord.lng}`,
            );

            if (!props.readonly) {
                marker.on('dragend', () => {
                    updateCoordinates();
                    updatePolygon();
                    if (areaData.coordinates.length >= 3) {
                        calculateArea();
                    }
                });
            }

            markers.push(marker);
        });

        console.log(`Total markers created: ${markers.length}`);
        console.log('All markers:', markers);

        updatePolygon();
        console.log('Polygon updated');

        if (areaData.coordinates.length >= 3) {
            calculateArea();
            console.log('Area calculated');
        }

        // Ajustar la vista del mapa para mostrar el área completa
        setTimeout(() => {
            console.log('Adjusting map view...');
            if (polygon) {
                console.log('Fitting bounds to polygon');
                console.log('Polygon bounds:', polygon.getBounds());
                map!.fitBounds(polygon.getBounds(), {
                    padding: [20, 20],
                    maxZoom: 16,
                });
            } else if (markers.length > 0) {
                console.log('Fitting bounds to markers');
                const group = new L.featureGroup(markers);
                console.log('Markers bounds:', group.getBounds());
                map!.fitBounds(group.getBounds(), {
                    padding: [20, 20],
                    maxZoom: 16,
                });
            }
            console.log('Map view adjusted');
            console.log('Current map center:', map!.getCenter());
            console.log('Current map zoom:', map!.getZoom());
        }, 500); // Increased timeout for better debugging
    } else {
        console.log('No coordinates provided or empty array');
    }
});

// Watch para cambios en las props iniciales
watch(
    () => props.initialCoordinates,
    (newCoords) => {
        if (newCoords && newCoords.length > 0) {
            clearArea();
            areaData.coordinates = [...newCoords];

            newCoords.forEach((coord) => {
                const marker = L.marker([coord.lat, coord.lng], {
                    draggable: !props.readonly,
                    icon: L.divIcon({
                        className: 'custom-marker',
                        html: `<div class="w-4 h-4 bg-blue-500 border-2 border-white rounded-full shadow-lg"></div>`,
                        iconSize: [16, 16],
                        iconAnchor: [8, 8],
                    }),
                }).addTo(map!);

                if (!props.readonly) {
                    marker.on('dragend', () => {
                        updateCoordinates();
                        updatePolygon();
                        if (areaData.coordinates.length >= 3) {
                            calculateArea();
                        }
                    });
                }

                markers.push(marker);
            });

            updatePolygon();
            if (areaData.coordinates.length >= 3) {
                calculateArea();
            }
        }
    },
    { deep: true },
);

// Exponer métodos para que puedan ser llamados desde el componente padre
defineExpose({
    clearArea,
    calculateArea,
    getAreaData: () => areaData,
});
</script>

<style scoped>
.land-area-selector {
    @apply flex h-full w-full flex-col;
}

#map {
    width: 100% !important;
    height: 400px !important;
    min-height: 400px !important;
    border-radius: 0.75rem;
    border: 2px solid #e5e7eb;
    background-color: #f9fafb;
}

/* Estilos optimizados para Leaflet */
:deep(.leaflet-container) {
    font-family: 'Fredoka', sans-serif;
    border-radius: 0.75rem;
    width: 100% !important;
    height: 100% !important;
}

:deep(.leaflet-popup-content-wrapper) {
    border-radius: 0.5rem;
    font-family: 'Fredoka', sans-serif;
}

:deep(.leaflet-control-layers) {
    border-radius: 0.5rem;
    font-family: 'Fredoka', sans-serif;
}

:deep(.leaflet-control-zoom a) {
    border-radius: 0.25rem;
}

/* Asegurar que los marcadores sean visibles */
:deep(.custom-marker) {
    z-index: 1000 !important;
}

/* Asegurar que el polígono sea visible */
:deep(.leaflet-interactive) {
    cursor: pointer !important;
}
</style>
