<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import MasonryGrid from '@/Components/MasonryGrid.vue';
import PhotoLightbox from '@/Components/PhotoLightbox.vue';
import gsap from 'gsap';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    featuredProjects: Array,
    highlightedPhotos: Array,
    settings: Object,
});

const heroRef = ref(null);
const activePhoto = ref(null);

onMounted(() => {
    // Hero animation sequence
    const tl = gsap.timeline({ delay: 0.3 });

    tl.from('.hero-line-1', {
        y: 60,
        opacity: 0,
        duration: 1,
        ease: 'power4.out',
    })
    .from('.hero-line-2', {
        y: 50,
        opacity: 0,
        duration: 0.9,
        ease: 'power4.out',
    }, '-=0.6')
    .from('.hero-subtitle', {
        y: 30,
        opacity: 0,
        duration: 0.7,
        ease: 'power3.out',
    }, '-=0.5')
    .from('.hero-cta', {
        y: 20,
        opacity: 0,
        duration: 0.6,
        ease: 'power3.out',
    }, '-=0.3')
    .from('.scroll-indicator', {
        opacity: 0,
        duration: 0.5,
    }, '-=0.2');
});

const openLightbox = (photo) => {
    activePhoto.value = photo;
};
</script>

<template>
    <Head>
        <title>Florian Sanchez | Photographe Professionnel</title>
        <meta name="description" content="Portfolio de Florian Sanchez, photographe professionnel spécialisé dans le portrait, le reportage et l'événementiel. Capturer l'instant, révéler l'émotion." />
        <meta property="og:title" content="Florian Sanchez | Photographe Professionnel" />
        <meta property="og:description" content="Découvrez mon univers photographique : portraits, reportages et shootings professionnels." />
    </Head>

    <!-- Hero Section -->
    <section class="hero" ref="heroRef">
        <div class="hero-bg">
            <div class="hero-gradient"></div>
            <div class="hero-particles">
                <div class="particle" v-for="i in 6" :key="i" :class="`p-${i}`"></div>
            </div>
        </div>

        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-line-1">Florian Sanchez</h1>
                <h2 class="hero-line-2">
                    <span class="gradient-text display-text">Photographe</span>
                </h2>
                <p class="hero-subtitle">
                    Capturer l'instant, révéler l'émotion. Chaque image raconte une histoire.
                </p>
                <div class="hero-cta">
                    <Link href="/projets" class="btn btn-primary btn-lg">
                        Découvrir mes projets
                    </Link>
                    <Link href="/contact" class="btn btn-ghost btn-lg">
                        Me contacter
                    </Link>
                </div>
            </div>
        </div>

        <div class="scroll-indicator">
            <div class="scroll-line"></div>
            <span>Scroll</span>
        </div>
    </section>

    <!-- Featured Projects -->
    <section class="section" v-if="featuredProjects.length > 0">
        <div class="container">
            <ScrollReveal>
                <div class="section-header">
                    <span class="section-eyebrow">Portfolio</span>
                    <h2>Projets récents</h2>
                    <p>Découvrez mes derniers shootings et projets photographiques</p>
                </div>
            </ScrollReveal>

            <div class="projects-grid">
                <ScrollReveal
                    v-for="(project, index) in featuredProjects"
                    :key="project.id"
                    :delay="index * 0.1"
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
                                <span class="btn btn-ghost btn-sm">Voir le projet</span>
                            </div>
                        </div>
                        <div class="project-card-info">
                            <span v-if="project.category" class="badge badge-primary">{{ project.category.name }}</span>
                            <h3>{{ project.title }}</h3>
                            <div class="project-card-meta">
                                <span v-if="project.date">{{ new Date(project.date).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }) }}</span>
                                <span v-if="project.photo_count">{{ project.photo_count }} photos</span>
                            </div>
                        </div>
                    </Link>
                </ScrollReveal>
            </div>

            <ScrollReveal class="text-center" :delay="0.3" style="margin-top: var(--space-2xl);">
                <Link href="/projets" class="btn btn-ghost btn-lg">
                    Tous les projets →
                </Link>
            </ScrollReveal>
        </div>
    </section>

    <!-- Highlighted Photos Section -->
    <section class="section" v-if="highlightedPhotos.length > 0" style="background: var(--bg-deep);">
        <div class="container container-wide">
            <ScrollReveal>
                <div class="section-header">
                    <span class="section-eyebrow">Sélection</span>
                    <h2>Coups de cœur</h2>
                </div>
            </ScrollReveal>

            <MasonryGrid
                :photos="highlightedPhotos"
                @photoClick="openLightbox"
            />

            <PhotoLightbox
                :photos="highlightedPhotos"
                :activePhoto="activePhoto"
                @close="activePhoto = null"
                @next="(p) => activePhoto = p"
                @prev="(p) => activePhoto = p"
            />
        </div>
    </section>

    <!-- About Teaser -->
    <section class="section">
        <div class="container">
            <div class="about-teaser">
                <ScrollReveal :x="-30" :y="0">
                    <div class="about-teaser-text">
                        <span class="section-eyebrow">À propos</span>
                        <h2>Passionné par la lumière</h2>
                        <p>
                            {{ settings?.bio_short || "Photographe passionné, je capture les moments qui racontent des histoires. De la photo de portrait à la photo urbaine, chaque cliché est une invitation au voyage visuel." }}
                        </p>
                        <Link href="/a-propos" class="btn btn-ghost">En savoir plus</Link>
                    </div>
                </ScrollReveal>
                <ScrollReveal :x="30" :y="0" :delay="0.2">
                    <div class="about-teaser-visual">
                        <img src="/images/florian-sanchez.jpg" alt="Florian Sanchez - Photographe" class="about-img" />
                    </div>
                </ScrollReveal>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-section">
        <div class="container text-center">
            <ScrollReveal>
                <h2 style="margin-bottom: var(--space-md);">Un projet en tête ?</h2>
                <p style="font-size: 1.1rem; margin-bottom: var(--space-xl);">
                    Discutons de votre prochain shooting
                </p>
                <Link href="/contact" class="btn btn-accent btn-lg">
                    Prendre contact
                </Link>
            </ScrollReveal>
        </div>
    </section>
</template>

<style scoped>
/* Hero */
.hero {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.hero-bg {
    position: absolute;
    inset: 0;
    z-index: 0;
}

.hero-gradient {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse at 20% 50%, var(--primary-glow-strong) 0%, transparent 50%),
        radial-gradient(ellipse at 80% 20%, var(--accent-glow) 0%, transparent 40%),
        radial-gradient(ellipse at 50% 80%, rgba(41, 62, 204, 0.08) 0%, transparent 50%);
}

.hero-particles {
    position: absolute;
    inset: 0;
}

.particle {
    position: absolute;
    border-radius: 50%;
    background: var(--primary);
    opacity: 0.08;
    animation: float 6s ease-in-out infinite;
}

.p-1 { width: 300px; height: 300px; top: 10%; left: -5%; animation-delay: 0s; }
.p-2 { width: 200px; height: 200px; top: 60%; right: 10%; animation-delay: -2s; background: var(--accent); }
.p-3 { width: 150px; height: 150px; bottom: 20%; left: 30%; animation-delay: -4s; }
.p-4 { width: 100px; height: 100px; top: 30%; right: 30%; animation-delay: -1s; background: var(--accent); opacity: 0.05; }
.p-5 { width: 80px; height: 80px; bottom: 10%; right: 20%; animation-delay: -3s; }
.p-6 { width: 250px; height: 250px; top: -5%; right: -5%; animation-delay: -5s; background: var(--accent); opacity: 0.04; }

.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 0 var(--space-xl);
}

.hero-line-1 {
    font-size: clamp(2.5rem, 7vw, 5rem);
    font-weight: 800;
    letter-spacing: -0.03em;
    margin-bottom: 0;
    line-height: 1.1;
}

.hero-line-2 {
    font-size: clamp(2rem, 5vw, 3.5rem);
    margin-bottom: var(--space-lg);
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.15rem;
    color: var(--text-muted);
    max-width: 500px;
    margin: 0 auto var(--space-2xl);
    line-height: 1.7;
}

.hero-cta {
    display: flex;
    gap: var(--space-md);
    justify-content: center;
    flex-wrap: wrap;
}

/* Scroll Indicator */
.scroll-indicator {
    position: absolute;
    bottom: 2rem;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-faint);
    font-size: 0.75rem;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.scroll-line {
    width: 1px;
    height: 40px;
    background: var(--primary);
    animation: scrollIndicator 2s ease-in-out infinite;
}

/* Section Header */
.section-header {
    text-align: center;
    margin-bottom: var(--space-3xl);
}

.section-eyebrow {
    display: inline-block;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 3px;
    color: var(--primary-light);
    margin-bottom: var(--space-sm);
}

.section-header h2 {
    margin-bottom: var(--space-sm);
}

.section-header p {
    color: var(--text-muted);
    max-width: 600px;
    margin: 0 auto;
}

/* Projects Grid */
.projects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: var(--space-xl);
}

.project-card {
    display: block;
    text-decoration: none;
    color: var(--text-color);
    border-radius: var(--radius-lg);
    overflow: hidden;
    transition: all var(--transition-base);
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

.project-card-meta {
    display: flex;
    gap: var(--space-lg);
    font-size: 0.85rem;
    color: var(--text-muted);
}

/* About Teaser */
.about-teaser {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-4xl);
    align-items: center;
}

.about-teaser-text .section-eyebrow {
    text-align: left;
}

.about-teaser-text h2 {
    text-align: left;
}

.about-teaser-text p {
    font-size: 1.05rem;
    line-height: 1.8;
    margin-bottom: var(--space-xl);
}

.about-teaser-visual {
    position: relative;
    height: 500px;
    border-radius: var(--radius-lg);
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    overflow: hidden;
    box-shadow: var(--shadow-lg);
}

.about-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(0.2) contrast(1.1);
    transition: transform 0.8s var(--ease-out);
}

.about-teaser-visual:hover .about-img {
    transform: scale(1.05);
}

/* CTA Section */
.cta-section {
    padding: var(--space-5xl) 0;
    background:
        linear-gradient(135deg, rgba(41, 62, 204, 0.05), rgba(157, 78, 221, 0.05));
    border-top: 1px solid var(--border-subtle);
    border-bottom: 1px solid var(--border-subtle);
}

@media (max-width: 768px) {
    .projects-grid {
        grid-template-columns: 1fr;
    }

    .about-teaser {
        grid-template-columns: 1fr;
        gap: var(--space-2xl);
    }

    .about-teaser-visual {
        height: 250px;
    }
}
</style>
