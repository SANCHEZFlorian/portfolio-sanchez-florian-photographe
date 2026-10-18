<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import gsap from 'gsap';
import { PhInstagramLogo } from '@phosphor-icons/vue';

const props = defineProps({
    photos: { type: Array, required: true },
    animated: { type: Boolean, default: true },
});

const emit = defineEmits(['photoClick']);

const gridRef = ref(null);
const currentCols = ref(12);
let resizeObserver = null;

const updateGridUnit = () => {
    if (!gridRef.value) return;
    const width = gridRef.value.clientWidth;
    if (!width) return;
    const gap = 16;
    const cols = width > 900 ? 12 : (width > 580 ? 6 : 1);
    currentCols.value = cols;
    const colWidth = (width - (cols - 1) * gap) / cols;
    gridRef.value.style.setProperty('--grid-unit', `${colWidth}px`);
    gridRef.value.style.setProperty('--grid-cols', cols);
};

const getPhotoStyle = (photo) => {
    // Only apply explicit col/row start on desktop 12-column layout to prevent breaking responsive flow
    if (currentCols.value === 12 && photo.col_start && photo.row_start) {
        let c = photo.span_cols || 3;
        let r = photo.span_rows || 2;
        if (c === 12 && r === 8) { c = 6; r = 4; }
        else if (c === 6 && r === 9) { c = 4; r = 6; }
        else if (c === 2 && r === 1) { c = 6; r = 4; }
        else if (c === 1 && r === 2) { c = 4; r = 6; }
        else if (c === 1 && r === 1) { c = 3; r = 2; }

        return {
            gridColumn: `${photo.col_start} / span ${c}`,
            gridRow: `${photo.row_start} / span ${r}`,
        };
    }
    return {};
};

const getPhotoClasses = (photo) => {
    let c = photo.span_cols || 3;
    let r = photo.span_rows || 2;

    // Rétrocompatibilité avec les versions précédentes
    if (c === 12 && r === 8) { c = 6; r = 4; } // Ancien Grand Paysage -> Grand Paysage 6x4
    else if (c === 6 && r === 9) { c = 4; r = 6; } // Ancien Grand Portrait -> Grand Portrait 4x6
    else if (c === 2 && r === 1) { c = 6; r = 4; } // Ancien 2x1 -> Grand Paysage
    else if (c === 1 && r === 2) { c = 4; r = 6; } // Ancien 1x2 -> Grand Portrait
    else if (c === 1 && r === 1) { c = 3; r = 2; } // Ancien 1x1 -> Petit Paysage

    return [`span-c-${c}`, `span-r-${r}`];
};

onMounted(() => {
    updateGridUnit();

    if (window.ResizeObserver && gridRef.value) {
        resizeObserver = new ResizeObserver(() => updateGridUnit());
        resizeObserver.observe(gridRef.value);
    }

    if (props.animated && gridRef.value) {
        gsap.from(gridRef.value.querySelectorAll('.grid-item'), {
            y: 35,
            opacity: 0,
            duration: 0.7,
            stagger: 0.04,
            ease: 'power3.out',
            delay: 0.1,
            clearProps: 'transform,opacity',
        });
    }
});

onUnmounted(() => {
    if (resizeObserver) {
        resizeObserver.disconnect();
    }
});

watch(() => props.photos, () => {
    updateGridUnit();
    if (props.animated && gridRef.value) {
        gsap.from(gridRef.value.querySelectorAll('.grid-item'), {
            y: 25,
            opacity: 0,
            duration: 0.5,
            stagger: 0.03,
            ease: 'power3.out',
            clearProps: 'transform,opacity',
        });
    }
}, { flush: 'post' });
</script>

<template>
    <div class="masonry-grid" ref="gridRef">
        <div
            v-for="photo in photos"
            :key="photo.id"
            class="grid-item"
            :class="getPhotoClasses(photo)"
            :style="getPhotoStyle(photo)"
            @click="emit('photoClick', photo)"
        >
            <div class="grid-image-container">
                <!-- Shimmer / Skeleton Loader -->
                <div class="image-skeleton"></div>

                <img
                    :src="`/storage/${photo.thumbnail_path || photo.image_path}`"
                    :alt="photo.caption || 'Photographie'"
                    loading="lazy"
                    @load="(e) => e.target.classList.add('loaded')"
                />
            </div>

            <div class="grid-overlay">
                <div class="overlay-content">
                    <span v-if="photo.project" class="overlay-project">{{ photo.project.title }}</span>
                    <span v-if="photo.caption" class="overlay-caption">{{ photo.caption }}</span>
                    <span v-if="photo.instagram_tag" class="overlay-insta">
                        <PhInstagramLogo :size="13" weight="fill" />
                        <span>{{ photo.instagram_tag.startsWith('@') ? photo.instagram_tag : '@' + photo.instagram_tag }}</span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.grid-image-container {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: var(--bg-deep);
}

.image-skeleton {
    position: absolute;
    inset: 0;
    background: linear-gradient(
        90deg,
        rgba(255, 255, 255, 0) 0,
        rgba(255, 255, 255, 0.05) 50%,
        rgba(255, 255, 255, 0) 100%
    );
    background-size: 200% 100%;
    animation: shimmer 1.5s infinite;
    z-index: 1;
}

@keyframes shimmer {
    0% { background-position: -200% 0; }
    100% { background-position: 200% 0; }
}

.grid-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    opacity: 0;
    transition: opacity 0.8s var(--ease-out), transform 0.6s var(--ease-out);
    position: relative;
    z-index: 2;
    display: block;
}

.grid-item img.loaded {
    opacity: 1;
}

.grid-item:hover img.loaded {
    transform: scale(1.03);
}

.overlay-content {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
}

.overlay-project {
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.overlay-caption {
    font-size: 0.85rem;
    color: var(--text-secondary);
}

.overlay-insta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--accent-light);
    background: rgba(157, 78, 221, 0.15);
    backdrop-filter: blur(4px);
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    border: 1px solid rgba(157, 78, 221, 0.2);
    width: fit-content;
    margin-top: 4px;
}
</style>
