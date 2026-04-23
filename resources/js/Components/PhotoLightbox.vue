<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    photos: { type: Array, required: true },
    activePhoto: { type: Object, default: null },
});

const emit = defineEmits(['close', 'next', 'prev']);

const currentIndex = ref(0);

watch(() => props.activePhoto, (photo) => {
    if (photo) {
        currentIndex.value = props.photos.findIndex(p => p.id === photo.id);
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

const goNext = () => {
    if (currentIndex.value < props.photos.length - 1) {
        emit('next', props.photos[currentIndex.value + 1]);
    }
};

const goPrev = () => {
    if (currentIndex.value > 0) {
        emit('prev', props.photos[currentIndex.value - 1]);
    }
};

const handleKeydown = (e) => {
    if (!props.activePhoto) return;
    if (e.key === 'Escape') emit('close');
    if (e.key === 'ArrowRight') goNext();
    if (e.key === 'ArrowLeft') goPrev();
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition name="lightbox-fade">
            <div v-if="activePhoto" class="lightbox" @click.self="emit('close')">
                <button class="lightbox-close" @click="emit('close')">&times;</button>

                <button
                    v-if="currentIndex > 0"
                    class="lightbox-nav nav-prev"
                    @click.stop="goPrev"
                >
                    &#10094;
                </button>

                <div class="lightbox-center">
                    <img
                        :src="`/storage/${activePhoto.image_path}`"
                        class="lightbox-img"
                        :key="activePhoto.id"
                        :alt="activePhoto.caption || 'Photo'"
                    />
                    <div class="lightbox-meta" v-if="activePhoto.caption || activePhoto.project || activePhoto.instagram_tag">
                        <h3 v-if="activePhoto.project">{{ activePhoto.project.title }}</h3>
                        <p v-if="activePhoto.caption">{{ activePhoto.caption }}</p>
                        <a
                            v-if="activePhoto.instagram_tag"
                            :href="`https://instagram.com/${activePhoto.instagram_tag.replace('@','')}`"
                            target="_blank"
                            rel="noopener"
                            class="lightbox-insta"
                        >
                            {{ activePhoto.instagram_tag }}
                        </a>
                    </div>
                    <div class="lightbox-counter">
                        {{ currentIndex + 1 }} / {{ photos.length }}
                    </div>
                </div>

                <button
                    v-if="currentIndex < photos.length - 1"
                    class="lightbox-nav nav-next"
                    @click.stop="goNext"
                >
                    &#10095;
                </button>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.lightbox-center {
    display: flex;
    flex-direction: column;
    align-items: center;
    max-width: 90vw;
    max-height: 90vh;
}

.lightbox-img {
    animation: lightboxImgIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes lightboxImgIn {
    0% { transform: scale(0.93); opacity: 0; }
    100% { transform: scale(1); opacity: 1; }
}

.lightbox-meta {
    margin-top: 1rem;
    text-align: center;
}

.lightbox-meta h3 {
    color: white;
    font-weight: 400;
    letter-spacing: 1px;
    margin-bottom: 0.3rem;
    font-size: 1.1rem;
}

.lightbox-meta p {
    color: var(--text-muted);
    font-size: 0.9rem;
    margin: 0;
}

.lightbox-insta {
    display: inline-block;
    margin-top: 0.5rem;
    color: var(--accent);
    font-size: 0.85rem;
    transition: color var(--transition-fast);
}

.lightbox-insta:hover {
    color: var(--accent-light);
}

.lightbox-counter {
    position: fixed;
    bottom: 2rem;
    font-size: 0.85rem;
    color: var(--text-muted);
    letter-spacing: 1px;
}
</style>
