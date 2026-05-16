<script setup>
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import MasonryGrid from '@/Components/MasonryGrid.vue';
import PhotoLightbox from '@/Components/PhotoLightbox.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    project: Object,
});

const activePhoto = ref(null);
</script>

<template>
    <Head>
        <title>{{ project.title }} | Florian Sanchez Photographe</title>
        <meta name="description" :content="project.description || 'Découvrez le projet photographique ' + project.title + ' par Florian Sanchez.'" />
        
        <!-- Open Graph -->
        <meta property="og:title" :content="project.title + ' | Florian Sanchez'" />
        <meta property="og:description" :content="project.description || 'Projet photographique professionnel.'" />
        <meta v-if="project.cover_url" property="og:image" :content="project.cover_url" />
        <meta property="og:type" content="article" />
        
        <!-- Twitter -->
        <meta name="twitter:title" :content="project.title + ' | Florian Sanchez'" />
        <meta name="twitter:description" :content="project.description || 'Découvrez mon dernier projet photographique.'" />
        <meta v-if="project.cover_url" name="twitter:image" :content="project.cover_url" />
    </Head>

    <!-- Project Header -->
    <section class="project-header">
        <div class="container">
            <ScrollReveal>
                <Link href="/projets" class="back-link">← Retour aux projets</Link>
            </ScrollReveal>

            <ScrollReveal :delay="0.1">
                <div class="project-title-block">
                    <span v-if="project.category" class="badge badge-primary">{{ project.category.name }}</span>
                    <h1>{{ project.title }}</h1>
                    <div class="project-meta">
                        <span v-if="project.location">📍 {{ project.location }}</span>
                        <span v-if="project.date">📅 {{ new Date(project.date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
                        <span>📷 {{ project.photos.length }} photos</span>
                    </div>
                    <p v-if="project.description" class="project-description">{{ project.description }}</p>
                    <a
                        v-if="project.instagram_url"
                        :href="project.instagram_url"
                        target="_blank"
                        rel="noopener"
                        class="insta-link"
                    >
                        Voir sur Instagram →
                    </a>
                </div>
            </ScrollReveal>
        </div>
    </section>

    <!-- Photos Gallery -->
    <section class="section-sm">
        <div class="container container-wide">
            <MasonryGrid
                v-if="project.photos.length > 0"
                :photos="project.photos"
                @photoClick="(p) => activePhoto = p"
            />

            <div v-else class="empty-state">
                <div class="empty-state-icon">📷</div>
                <div class="empty-state-title">Galerie en préparation</div>
                <div class="empty-state-text">Les photos de ce projet arrivent bientôt.</div>
            </div>

            <PhotoLightbox
                :photos="project.photos"
                :activePhoto="activePhoto"
                @close="activePhoto = null"
                @next="(p) => activePhoto = p"
                @prev="(p) => activePhoto = p"
            />
        </div>
    </section>
</template>

<style scoped>
.project-header {
    padding-top: var(--space-3xl);
    padding-bottom: var(--space-xl);
}

.back-link {
    display: inline-block;
    color: var(--text-muted);
    font-size: 0.9rem;
    margin-bottom: var(--space-xl);
    transition: color var(--transition-base);
}

.back-link:hover {
    color: var(--primary-light);
}

.project-title-block h1 {
    font-size: clamp(2rem, 4vw, 3.5rem);
    margin-bottom: var(--space-md);
}

.project-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-xl);
    font-size: 0.95rem;
    color: var(--text-muted);
    margin-bottom: var(--space-lg);
}

.project-description {
    font-size: 1.05rem;
    line-height: 1.8;
    max-width: 700px;
    margin-bottom: var(--space-md);
}

.insta-link {
    display: inline-block;
    color: var(--accent);
    font-size: 0.95rem;
    transition: color var(--transition-base);
}

.insta-link:hover {
    color: var(--accent-light);
}

.project-title-block .badge {
    margin-bottom: var(--space-sm);
}
</style>
