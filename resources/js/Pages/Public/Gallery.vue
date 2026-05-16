<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import MasonryGrid from '@/Components/MasonryGrid.vue';
import PhotoLightbox from '@/Components/PhotoLightbox.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    photos: Array,
    categories: Array,
    currentCategory: String,
});

const activePhoto = ref(null);

const setCategory = (slug) => {
    router.get('/galerie', { category: slug }, { 
        preserveState: true,
        preserveScroll: true,
        only: ['photos', 'currentCategory']
    });
};
</script>

<template>
    <Head>
        <title>Galerie Photo & Instants | Florian Sanchez Photographe</title>
        <meta name="description" content="Une immersion visuelle dans mes coups de cœur photographiques. Portraits, paysages et photographie de rue." />
        <meta property="og:title" content="Galerie Photo | Florian Sanchez Photographe" />
        <meta property="og:description" content="Découvrez une sélection de mes clichés favoris." />
    </Head>

    <section class="gallery-hero">
        <div class="container">
            <ScrollReveal>
                <h1 class="page-title">Galerie</h1>
                <p class="page-subtitle">Instants saisis, hors du temps et des projets.</p>
            </ScrollReveal>

            <!-- Filters -->
            <ScrollReveal :delay="0.1">
                <div class="filters">
                    <button 
                        class="filter-btn" 
                        :class="{ active: currentCategory === 'all' }"
                        @click="setCategory('all')"
                    >
                        Tout
                    </button>
                    <button 
                        v-for="cat in categories" 
                        :key="cat.id" 
                        class="filter-btn"
                        :class="{ active: currentCategory === cat.slug }"
                        @click="setCategory(cat.slug)"
                    >
                        {{ cat.name }}
                    </button>
                </div>
            </ScrollReveal>
        </div>
    </section>

    <section class="section-sm">
        <div class="container container-wide">
            <MasonryGrid 
                v-if="photos.length > 0"
                :photos="photos" 
                @photoClick="(p) => activePhoto = p"
            />

            <div v-else class="empty-state">
                <div class="empty-state-icon">📷</div>
                <div class="empty-state-title">Aucune photo trouvée</div>
                <div class="empty-state-text">D'autres clichés arrivent très bientôt dans cet espace.</div>
            </div>

            <PhotoLightbox 
                :photos="photos"
                :activePhoto="activePhoto"
                @close="activePhoto = null"
                @next="(p) => activePhoto = p"
                @prev="(p) => activePhoto = p"
            />
        </div>
    </section>
</template>

<style scoped>
.gallery-hero {
    padding-top: var(--space-3xl);
    padding-bottom: var(--space-xl);
    text-align: center;
}

.page-title {
    font-size: clamp(2.5rem, 6vw, 4rem);
    margin-bottom: var(--space-md);
}

.page-subtitle {
    color: var(--text-muted);
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto var(--space-2xl);
}

.filters {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: var(--space-md);
}

.filter-btn {
    padding: 0.6rem 1.2rem;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-full);
    color: var(--text-secondary);
    font-size: 0.9rem;
    cursor: pointer;
    transition: all var(--transition-base);
}

.filter-btn:hover {
    border-color: var(--primary);
    color: var(--primary-light);
}

.filter-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    box-shadow: 0 4px 15px var(--primary-glow);
}
</style>
