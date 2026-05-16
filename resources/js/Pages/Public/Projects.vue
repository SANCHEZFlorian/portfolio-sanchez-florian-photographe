<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import CategoryFilter from '@/Components/CategoryFilter.vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    projects: Array,
    categories: Array,
    currentCategory: String,
});

const filterCategory = (slug) => {
    router.get('/projets', slug === 'all' ? {} : { category: slug }, {
        preserveState: true,
        preserveScroll: false,
    });
};
</script>

<template>
    <Head>
        <title>Portfolio & Projets | Florian Sanchez Photographe</title>
        <meta name="description" content="Explorez mes projets photographiques : shootings mode, portraits, reportages et événements. Chaque projet capture un instant unique." />
        <meta property="og:title" content="Portfolio & Projets | Florian Sanchez Photographe" />
        <meta property="og:description" content="Découvrez mes derniers travaux et reportages photographiques." />
    </Head>

    <section class="section">
        <div class="container">
            <ScrollReveal>
                <div class="page-hero">
                    <span class="section-eyebrow">Mes travaux</span>
                    <h1>Projets</h1>
                    <p class="page-hero-sub">Chaque projet est une histoire, un moment capturé dans le temps.</p>
                </div>
            </ScrollReveal>

            <ScrollReveal :delay="0.15">
                <CategoryFilter
                    :categories="categories"
                    :currentCategory="currentCategory"
                    @filter="filterCategory"
                />
            </ScrollReveal>

            <div class="projects-list">
                <ScrollReveal
                    v-for="(project, index) in projects"
                    :key="project.id"
                    :delay="index * 0.08"
                >
                    <Link :href="`/projets/${project.slug}`" class="project-card">
                        <div class="project-card-image">
                            <img
                                v-if="project.cover_url"
                                :src="project.cover_url"
                                :alt="project.title"
                                loading="lazy"
                            />
                            <div v-else class="project-card-placeholder">
                                <span>📷</span>
                            </div>
                            <div class="project-card-overlay">
                                <span class="btn btn-ghost btn-sm">Voir le projet →</span>
                            </div>
                        </div>
                        <div class="project-card-info">
                            <span v-if="project.category" class="badge badge-primary">{{ project.category.name }}</span>
                            <h3>{{ project.title }}</h3>
                            <p v-if="project.description" class="project-desc">{{ project.description }}</p>
                            <div class="project-card-meta">
                                <span v-if="project.location">📍 {{ project.location }}</span>
                                <span v-if="project.date">{{ new Date(project.date).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }) }}</span>
                                <span v-if="project.photo_count">{{ project.photo_count }} photos</span>
                            </div>
                        </div>
                    </Link>
                </ScrollReveal>
            </div>

            <div v-if="projects.length === 0" class="empty-state">
                <div class="empty-state-icon">📷</div>
                <div class="empty-state-title">Aucun projet pour le moment</div>
                <div class="empty-state-text">De nouveaux projets arrivent bientôt !</div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.page-hero {
    text-align: center;
    margin-bottom: var(--space-3xl);
    padding-top: var(--space-2xl);
}

.page-hero h1 {
    font-size: 3rem;
    margin-bottom: var(--space-sm);
}

.page-hero-sub {
    font-size: 1.1rem;
    color: var(--text-muted);
    max-width: 500px;
    margin: 0 auto;
}

.projects-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: var(--space-xl);
}

.project-card {
    display: block;
    text-decoration: none;
    color: var(--text-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all 0.4s var(--ease-out);
    background: var(--surface-color);
    border: 1px solid var(--border-subtle);
}

.project-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 48px rgba(41, 62, 204, 0.12);
    border-color: rgba(41, 62, 204, 0.25);
}

.project-card-image {
    position: relative;
    aspect-ratio: 16/10;
    overflow: hidden;
}

.project-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.6s var(--ease-out);
}

.project-card:hover .project-card-image img {
    transform: scale(1.06);
}

.project-card-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg-elevated);
    font-size: 3rem;
    opacity: 0.3;
}

.project-card-overlay {
    position: absolute;
    inset: 0;
    background: rgba(8, 9, 26, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.4s var(--ease-out);
}

.project-card:hover .project-card-overlay {
    opacity: 1;
}

.project-card-info {
    padding: var(--space-lg);
}

.project-card-info .badge {
    margin-bottom: var(--space-sm);
}

.project-card-info h3 {
    font-size: 1.2rem;
    margin-bottom: var(--space-xs);
}

.project-desc {
    font-size: 0.9rem;
    color: var(--text-muted);
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-bottom: var(--space-sm);
}

.project-card-meta {
    display: flex;
    gap: var(--space-lg);
    font-size: 0.85rem;
    color: var(--text-muted);
}

@media (max-width: 768px) {
    .projects-list {
        grid-template-columns: 1fr;
    }

    .page-hero h1 {
        font-size: 2rem;
    }
}
</style>
