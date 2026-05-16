<script setup>
import { ref, onMounted, watch } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    photos: { type: Array, required: true },
    animated: { type: Boolean, default: true },
});

const emit = defineEmits(['photoClick']);

const gridRef = ref(null);

onMounted(() => {
    if (props.animated && gridRef.value) {
        gsap.from(gridRef.value.querySelectorAll('.grid-item'), {
            y: 40,
            opacity: 0,
            duration: 0.8,
            stagger: 0.08,
            ease: 'power3.out',
            delay: 0.2,
        });
    }
});

watch(() => props.photos, () => {
    if (props.animated && gridRef.value) {
        gsap.from(gridRef.value.querySelectorAll('.grid-item'), {
            y: 30,
            opacity: 0,
            duration: 0.6,
            stagger: 0.05,
            ease: 'power3.out',
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
            :class="[`span-c-${photo.span_cols || 1}`, `span-r-${photo.span_rows || 1}`]"
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
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                        {{ photo.instagram_tag.startsWith('@') ? photo.instagram_tag : '@' + photo.instagram_tag }}
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
    opacity: 0;
    transition: opacity 1s var(--ease-out), transform 0.6s var(--ease-out);
    position: relative;
    z-index: 2;
}

.grid-item img.loaded {
    opacity: 1;
}

.grid-item:hover img.loaded {
    transform: scale(1.05);
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
