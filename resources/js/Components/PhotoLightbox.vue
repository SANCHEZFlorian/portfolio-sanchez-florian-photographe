<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';
import {
    PhX,
    PhCaretLeft,
    PhCaretRight,
    PhInstagramLogo,
    PhCornersOut,
    PhArrowsIn,
    PhCamera,
    PhDownloadSimple,
} from '@phosphor-icons/vue';

const props = defineProps({
    photos: { type: Array, required: true },
    activePhoto: { type: Object, default: null },
    deliveryToken: { type: String, default: null },
});

const emit = defineEmits(['close', 'next', 'prev']);

const currentIndex = ref(0);
const isFullscreen = ref(false);
const isImageLoaded = ref(false);

// Support tactile mobile (Touch Swipe)
let touchStartX = 0;
let touchStartY = 0;

watch(() => props.activePhoto, (photo) => {
    if (photo) {
        currentIndex.value = props.photos.findIndex(p => p.id === photo.id);
        isImageLoaded.value = false;
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
        if (document.fullscreenElement) {
            document.exitFullscreen().catch(() => {});
        }
    }
});

const goNext = () => {
    if (currentIndex.value < props.photos.length - 1) {
        isImageLoaded.value = false;
        emit('next', props.photos[currentIndex.value + 1]);
    }
};

const goPrev = () => {
    if (currentIndex.value > 0) {
        isImageLoaded.value = false;
        emit('prev', props.photos[currentIndex.value - 1]);
    }
};

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        document.exitFullscreen().catch(() => {});
        isFullscreen.value = false;
    }
};

const handleKeydown = (e) => {
    if (!props.activePhoto) return;
    if (e.key === 'Escape') emit('close');
    if (e.key === 'ArrowRight') goNext();
    if (e.key === 'ArrowLeft') goPrev();
    if (e.key.toLowerCase() === 'f') toggleFullscreen();
};

const handleTouchStart = (e) => {
    touchStartX = e.touches[0].clientX;
    touchStartY = e.touches[0].clientY;
};

const handleTouchEnd = (e) => {
    const diffX = e.changedTouches[0].clientX - touchStartX;
    const diffY = e.changedTouches[0].clientY - touchStartY;

    // Détection de glissement horizontal dominant
    if (Math.abs(diffX) > 45 && Math.abs(diffX) > Math.abs(diffY)) {
        if (diffX < 0) {
            goNext();
        } else {
            goPrev();
        }
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    document.addEventListener('fullscreenchange', () => {
        isFullscreen.value = !!document.fullscreenElement;
    });
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <Transition name="lightbox-aperture">
            <div
                v-if="activePhoto"
                class="lightbox-backdrop"
                @click.self="emit('close')"
                @touchstart="handleTouchStart"
                @touchend="handleTouchEnd"
            >
                <!-- Éclair de diaphragme / Shutter Flash cinématique au déclenchement -->
                <div class="shutter-flash"></div>

                <!-- Barre d'outils supérieure -->
                <div class="lightbox-topbar">
                    <div class="viewfinder-tag">
                        <PhCamera :size="16" weight="duotone" class="text-accent" />
                        <span>Photo {{ currentIndex + 1 }} / {{ photos.length }}</span>
                    </div>

                    <div class="lightbox-actions">
                        <a
                            v-if="deliveryToken"
                            :href="`/livraison/${deliveryToken}/photos/${activePhoto.id}/telecharger`"
                            class="action-btn download-btn"
                            title="Télécharger cette photo en haute définition"
                            download
                        >
                            <PhDownloadSimple :size="19" weight="bold" />
                            <span class="download-label">Télécharger HD</span>
                        </a>

                        <button
                            class="action-btn"
                            @click="toggleFullscreen"
                            :title="isFullscreen ? 'Quitter le plein écran (F)' : 'Plein écran (F)'"
                        >
                            <component :is="isFullscreen ? PhArrowsIn : PhCornersOut" :size="20" weight="bold" />
                        </button>
                        <button
                            class="action-btn action-close"
                            @click="emit('close')"
                            title="Fermer (Échap)"
                        >
                            <PhX :size="22" weight="bold" />
                        </button>
                    </div>
                </div>

                <!-- Navigation Précédent -->
                <button
                    v-if="currentIndex > 0"
                    class="lightbox-nav nav-prev"
                    @click.stop="goPrev"
                    title="Photo précédente (←)"
                >
                    <PhCaretLeft :size="28" weight="bold" />
                </button>

                <!-- Zone Centrale : Image et Métadonnées -->
                <div class="lightbox-stage">
                    <div class="image-wrapper" :class="{ 'is-loading': !isImageLoaded }">
                        <!-- Halo optique d'exposition centré derrière l'image -->
                        <div class="exposure-halo"></div>

                        <img
                            :src="`/storage/${activePhoto.image_path}`"
                            class="lightbox-img"
                            :key="activePhoto.id"
                            :alt="activePhoto.caption || 'Photographie'"
                            @contextmenu.prevent
                            @dragstart.prevent
                            draggable="false"
                            @load="isImageLoaded = true"
                        />
                    </div>

                    <!-- Métadonnées & Légende stylisée -->
                    <div class="lightbox-meta" v-if="activePhoto.caption || activePhoto.project || activePhoto.instagram_tag">
                        <div class="meta-card">
                            <h3 v-if="activePhoto.project">{{ activePhoto.project.title }}</h3>
                            <p v-if="activePhoto.caption">{{ activePhoto.caption }}</p>
                            <a
                                v-if="activePhoto.instagram_tag"
                                :href="`https://instagram.com/${activePhoto.instagram_tag.replace('@','')}`"
                                target="_blank"
                                rel="noopener"
                                class="lightbox-insta"
                            >
                                <PhInstagramLogo :size="15" weight="fill" />
                                <span>{{ activePhoto.instagram_tag.startsWith('@') ? activePhoto.instagram_tag : '@' + activePhoto.instagram_tag }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Navigation Suivant -->
                <button
                    v-if="currentIndex < photos.length - 1"
                    class="lightbox-nav nav-next"
                    @click.stop="goNext"
                    title="Photo suivante (→)"
                >
                    <PhCaretRight :size="28" weight="bold" />
                </button>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.lightbox-backdrop {
    position: fixed;
    inset: 0;
    background: radial-gradient(circle at center, rgba(14, 18, 42, 0.94) 0%, rgba(6, 7, 18, 0.98) 100%);
    backdrop-filter: blur(28px) saturate(180%);
    -webkit-backdrop-filter: blur(28px) saturate(180%);
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    overflow: hidden;
}

/* Flash de l'obturateur à l'ouverture */
.shutter-flash {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, rgba(181, 101, 247, 0.25) 0%, rgba(58, 82, 224, 0.15) 45%, transparent 75%);
    pointer-events: none;
    animation: flashIn 0.5s ease-out forwards;
    z-index: 1;
}

@keyframes flashIn {
    0% { opacity: 1; transform: scale(0.9); }
    100% { opacity: 0; transform: scale(1.1); }
}

/* Barre supérieure */
.lightbox-topbar {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 70px;
    padding: 0 var(--space-2xl);
    display: flex;
    align-items: center;
    justify-content: space-between;
    z-index: 10;
    background: linear-gradient(to bottom, rgba(8, 9, 26, 0.8), transparent);
}

.viewfinder-tag {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: monospace;
    font-size: 0.85rem;
    letter-spacing: 2px;
    color: var(--text-secondary);
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    padding: 0.35rem 0.85rem;
    border-radius: var(--radius-full);
    backdrop-filter: blur(8px);
}

.text-accent {
    color: var(--accent-light);
}

.lightbox-actions {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.action-btn {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.25s var(--ease-out);
    backdrop-filter: blur(10px);
}

.action-btn:hover {
    background: rgba(255, 255, 255, 0.14);
    color: white;
    transform: scale(1.08);
    border-color: rgba(157, 78, 221, 0.5);
    box-shadow: 0 0 15px rgba(157, 78, 221, 0.3);
}

.action-close:hover {
    color: #f87171;
    border-color: rgba(239, 68, 68, 0.4);
    box-shadow: 0 0 15px rgba(239, 68, 68, 0.3);
}

.download-btn {
    width: auto;
    padding: 0 1rem;
    border-radius: var(--radius-full);
    background: linear-gradient(135deg, rgba(121, 40, 202, 0.4) 0%, rgba(67, 56, 202, 0.4) 100%);
    border: 1px solid rgba(157, 78, 221, 0.4);
    color: #fff;
    text-decoration: none;
    gap: 8px;
    font-size: 0.85rem;
    font-weight: 500;
}

.download-btn:hover {
    background: linear-gradient(135deg, rgba(121, 40, 202, 0.8) 0%, rgba(67, 56, 202, 0.8) 100%);
    border-color: rgba(157, 78, 221, 0.8);
    box-shadow: 0 0 20px rgba(157, 78, 221, 0.4);
    transform: translateY(-1px);
}

.download-label {
    display: inline;
}

@media (max-width: 640px) {
    .download-label {
        display: none;
    }
    .download-btn {
        width: 42px;
        padding: 0;
    }
}

.lightbox-img {
    user-select: none;
    -webkit-user-select: none;
    -webkit-user-drag: none;
}

/* Boutons de navigation Flèches */
.lightbox-nav {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 52px;
    height: 52px;
    border-radius: 50%;
    background: rgba(14, 18, 42, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.14);
    color: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: 10;
    backdrop-filter: blur(14px);
}

.lightbox-nav:hover {
    background: linear-gradient(135deg, rgba(41, 62, 204, 0.5), rgba(157, 78, 221, 0.5));
    border-color: rgba(181, 101, 247, 0.6);
    color: white;
    transform: translateY(-50%) scale(1.12);
    box-shadow: 0 0 25px rgba(157, 78, 221, 0.4);
}

.lightbox-nav.nav-prev {
    left: var(--space-2xl);
}

.lightbox-nav.nav-next {
    right: var(--space-2xl);
}

/* Zone d'affichage */
.lightbox-stage {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    max-width: 90vw;
    max-height: 88vh;
    position: relative;
    z-index: 2;
}

.image-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
}

.exposure-halo {
    position: absolute;
    inset: -30px;
    background: radial-gradient(circle, rgba(157, 78, 221, 0.2) 0%, rgba(41, 62, 204, 0.1) 40%, transparent 70%);
    filter: blur(40px);
    pointer-events: none;
    z-index: 0;
}

.lightbox-img {
    max-height: 76vh;
    max-width: 86vw;
    border-radius: 6px;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.75), 0 0 40px rgba(41, 62, 204, 0.2);
    object-fit: contain;
    position: relative;
    z-index: 1;
    animation: imageFocusIn 0.45s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

@keyframes imageFocusIn {
    0% {
        opacity: 0;
        transform: scale(0.92);
        filter: blur(14px) brightness(1.2);
    }
    100% {
        opacity: 1;
        transform: scale(1);
        filter: blur(0px) brightness(1);
    }
}

/* Métadonnées */
.lightbox-meta {
    margin-top: var(--space-md);
    position: relative;
    z-index: 3;
    animation: fadeIn 0.4s ease forwards;
}

.meta-card {
    background: rgba(14, 18, 42, 0.75);
    border: 1px solid rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(16px);
    border-radius: var(--radius-full);
    padding: 0.5rem 1.4rem;
    display: flex;
    align-items: center;
    gap: var(--space-md);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
}

.meta-card h3 {
    font-size: 0.95rem;
    font-weight: 600;
    color: white;
    margin: 0;
    letter-spacing: 0.5px;
}

.meta-card p {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin: 0;
}

.lightbox-insta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--accent-light);
    background: rgba(157, 78, 221, 0.18);
    border: 1px solid rgba(157, 78, 221, 0.3);
    padding: 0.2rem 0.65rem;
    border-radius: var(--radius-full);
    transition: all 0.2s ease;
}

.lightbox-insta:hover {
    background: rgba(157, 78, 221, 0.35);
    color: white;
    transform: translateY(-1px);
}

/* Transitions de la modale */
.lightbox-aperture-enter-active,
.lightbox-aperture-leave-active {
    transition: opacity 0.35s cubic-bezier(0.16, 1, 0.3, 1), transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
}

.lightbox-aperture-enter-from,
.lightbox-aperture-leave-to {
    opacity: 0;
    transform: scale(0.97);
}

@media (max-width: 768px) {
    .lightbox-topbar {
        padding: 0 var(--space-md);
    }
    .lightbox-nav.nav-prev {
        left: var(--space-sm);
    }
    .lightbox-nav.nav-next {
        right: var(--space-sm);
    }
    .lightbox-img {
        max-height: 70vh;
        max-width: 92vw;
    }
    .meta-card {
        border-radius: var(--radius-lg);
        flex-direction: column;
        gap: 4px;
        padding: 0.6rem 1rem;
        text-align: center;
    }
}
</style>
