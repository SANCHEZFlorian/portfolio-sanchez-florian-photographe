<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import MasonryGrid from '@/Components/MasonryGrid.vue';
import PhotoLightbox from '@/Components/PhotoLightbox.vue';
import gsap from 'gsap';
import { PhInstagramLogo, PhArrowRight, PhCamera, PhPawPrint, PhMountains, PhCar, PhMotorcycle } from '@phosphor-icons/vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    featuredProjects: Array,
    highlightedPhotos: Array,
    settings: Object,
});

const getInstaUsername = (url) => {
    if (!url) return '';
    const parts = url.replace(/\/$/, '').split('/');
    return '@' + parts[parts.length - 1];
};

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
                        <span>Découvrir mes projets</span>
                        <PhArrowRight :size="17" weight="bold" />
                    </Link>
                    <Link href="/contact" class="btn btn-ghost btn-lg">
                        <span>Me contacter</span>
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
                            <div class="project-card-main">
                                <span v-if="project.category" class="badge badge-primary">{{ project.category.name }}</span>
                                <h3>{{ project.title }}</h3>
                            </div>
                            
                            <div class="project-card-footer">
                                <div class="project-card-meta">
                                    <span v-if="project.date">{{ new Date(project.date).toLocaleDateString('fr-FR', { month: 'long', year: 'numeric' }) }}</span>
                                    <span v-if="project.photo_count">{{ project.photo_count }} photos</span>
                                </div>
                                <div v-if="project.instagram_url" class="project-card-insta" @click.stop.prevent="window.open(project.instagram_url, '_blank')">
                                    <PhInstagramLogo :size="14" weight="fill" />
                                    <span>{{ getInstaUsername(project.instagram_url) }}</span>
                                </div>
                            </div>
                        </div>
                    </Link>
                </ScrollReveal>
            </div>

            <ScrollReveal class="text-center" :delay="0.3" style="margin-top: var(--space-2xl);">
                <Link href="/projets" class="btn btn-ghost btn-lg" style="display: inline-flex; align-items: center; gap: 8px;">
                    <span>Tous les projets</span>
                    <PhArrowRight :size="16" weight="bold" />
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
    <section class="section about-section-wrap">
        <div class="container">
            <div class="about-teaser">
                <ScrollReveal :x="-30" :y="0">
                    <div class="about-teaser-text">
                        <span class="section-eyebrow">À propos</span>
                        <h2 class="about-headline">
                            Passionné par la <span class="gradient-text display-text">lumière</span>
                        </h2>

                        <div class="about-quote-box">
                            <span class="about-quote-symbol">“</span>
                            <p class="about-quote-text">
                                Saisir l'instinct animal, la force des grands paysages et l'énergie brute de la mécanique.
                            </p>
                        </div>

                        <p class="about-body-text">
                            {{ settings?.bio_short || "Photographe passionné, je pose mon objectif sur ce qui me fait vibrer : la beauté et la vivacité du monde animal, l'immensité des paysages naturels ainsi que les lignes et la puissance des voitures et des motos. Chaque cliché est une quête de lumière, de textures et d'instants authentiques." }}
                        </p>

                        <!-- Univers & Spécialités authentiques -->
                        <div class="about-specialties-grid">
                            <div class="specialty-card">
                                <div class="specialty-icon-wrap">
                                    <PhPawPrint :size="18" weight="fill" />
                                </div>
                                <div class="specialty-info">
                                    <span class="specialty-title">Animaux</span>
                                    <span class="specialty-desc">Faune sauvage & instants de vie</span>
                                </div>
                            </div>
                            <div class="specialty-card">
                                <div class="specialty-icon-wrap">
                                    <PhMountains :size="18" weight="fill" />
                                </div>
                                <div class="specialty-info">
                                    <span class="specialty-title">Paysages</span>
                                    <span class="specialty-desc">Grands espaces & lumières naturelles</span>
                                </div>
                            </div>
                            <div class="specialty-card">
                                <div class="specialty-icon-wrap">
                                    <PhCar :size="18" weight="fill" />
                                </div>
                                <div class="specialty-info">
                                    <span class="specialty-title">Automobile</span>
                                    <span class="specialty-desc">Lignes, courbes & aérodynamisme</span>
                                </div>
                            </div>
                            <div class="specialty-card">
                                <div class="specialty-icon-wrap">
                                    <PhMotorcycle :size="18" weight="fill" />
                                </div>
                                <div class="specialty-info">
                                    <span class="specialty-title">Motos</span>
                                    <span class="specialty-desc">Esprit mécanique & passion deux-roues</span>
                                </div>
                            </div>
                        </div>

                        <div class="about-action">
                            <Link href="/a-propos" class="btn btn-ghost about-btn">
                                <span>En savoir plus sur mon univers</span>
                                <PhArrowRight :size="16" weight="bold" />
                            </Link>
                        </div>
                    </div>
                </ScrollReveal>
                <ScrollReveal :x="30" :y="0" :delay="0.2">
                    <div class="about-teaser-visual">
                        <div class="about-img-frame">
                            <img src="/images/florian-sanchez.jpg" alt="Florian Sanchez - Photographe" class="about-img" />
                            <div class="about-frame-border"></div>
                        </div>
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
        radial-gradient(ellipse at 25% 45%, var(--primary-glow-strong) 0%, transparent 60%),
        radial-gradient(ellipse at 75% 25%, var(--accent-glow) 0%, transparent 50%),
        radial-gradient(ellipse at 50% 85%, rgba(41, 62, 204, 0.12) 0%, transparent 65%);
    pointer-events: none;
}

.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
    padding: 0 var(--space-xl);
}

.hero-line-1 {
    font-family: var(--font-display);
    font-size: clamp(2.8rem, 7.5vw, 5.2rem);
    font-weight: 500;
    letter-spacing: 0.02em;
    color: #f7f8fe;
    text-shadow: 0 4px 35px rgba(41, 62, 204, 0.22);
    margin-bottom: var(--space-xs);
    line-height: 1.12;
}

.hero-line-2 {
    font-size: clamp(2.2rem, 5.5vw, 3.8rem);
    margin-bottom: var(--space-lg);
    line-height: 1.2;
}

.hero-subtitle {
    font-size: 1.16rem;
    color: var(--text-secondary);
    max-width: 520px;
    margin: 0 auto var(--space-2xl);
    line-height: 1.75;
    letter-spacing: 0.01em;
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
    display: flex;
    flex-direction: column;
    height: 100%;
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
    display: flex;
    flex-direction: column;
    flex: 1;
}

.project-card-main {
    margin-bottom: var(--space-md);
}

.project-card-footer {
    margin-top: auto;
    padding-top: var(--space-md);
    border-top: 1px solid var(--border-subtle);
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
    margin-bottom: var(--space-sm);
}

.project-card-insta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    color: var(--accent-light);
    font-weight: 500;
    transition: color var(--transition-fast);
}

.project-card:hover .project-card-insta {
    color: var(--accent);
}

/* About Teaser */
.about-section-wrap {
    position: relative;
    overflow: hidden;
}

.about-teaser {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: var(--space-4xl);
    align-items: center;
}

.about-teaser-text .section-eyebrow {
    text-align: left;
    margin-bottom: var(--space-xs);
}

.about-headline {
    text-align: left;
    font-size: clamp(2.2rem, 4vw, 3rem);
    font-weight: 500;
    line-height: 1.2;
    margin-bottom: var(--space-lg);
    color: #f5f6fe;
}

.about-quote-box {
    position: relative;
    padding: var(--space-md) var(--space-lg) var(--space-md) var(--space-xl);
    background: linear-gradient(135deg, rgba(20, 25, 55, 0.55), rgba(12, 15, 35, 0.65));
    border-left: 3px solid var(--accent);
    border-radius: 0 var(--radius-md) var(--radius-md) 0;
    margin-bottom: var(--space-lg);
    backdrop-filter: blur(10px);
    border-top: 1px solid rgba(255, 255, 255, 0.05);
    border-right: 1px solid rgba(255, 255, 255, 0.05);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.about-quote-symbol {
    position: absolute;
    top: -12px;
    left: 8px;
    font-family: var(--font-display);
    font-size: 2.8rem;
    color: rgba(157, 78, 221, 0.28);
    line-height: 1;
    pointer-events: none;
}

.about-quote-text {
    font-family: var(--font-display);
    font-style: italic;
    font-size: 1.08rem;
    line-height: 1.6;
    color: #e2e5fc;
    margin-bottom: 0;
}

.about-body-text {
    font-size: 1.05rem;
    line-height: 1.85;
    color: var(--text-secondary);
    margin-bottom: var(--space-xl);
}

/* Univers & Spécialités */
.about-specialties-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-md);
    margin-bottom: var(--space-2xl);
}

.specialty-card {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: 0.85rem 1.1rem;
    background: rgba(16, 20, 46, 0.55);
    border: 1px solid rgba(255, 255, 255, 0.07);
    border-radius: var(--radius-lg);
    backdrop-filter: blur(8px);
    transition: all var(--transition-base);
}

.specialty-card:hover {
    border-color: rgba(160, 185, 255, 0.28);
    background: rgba(22, 27, 60, 0.75);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
}

.specialty-icon-wrap {
    width: 38px;
    height: 38px;
    border-radius: var(--radius-md);
    background: linear-gradient(135deg, rgba(41, 62, 204, 0.25), rgba(157, 78, 221, 0.25));
    border: 1px solid rgba(165, 185, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #c4b5fd;
    flex-shrink: 0;
    transition: transform var(--transition-fast);
}

.specialty-card:hover .specialty-icon-wrap {
    transform: scale(1.08);
    color: #ffffff;
    border-color: rgba(181, 101, 247, 0.45);
}

.specialty-info {
    display: flex;
    flex-direction: column;
}

.specialty-title {
    font-size: 0.98rem;
    font-weight: 600;
    color: #f7f8fe;
    letter-spacing: 0.01em;
}

.specialty-desc {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.about-action {
    display: flex;
}

.about-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-sm);
    padding: 0.8rem 1.8rem;
    font-weight: 500;
    color: #f0f2fe;
}

/* Visuel photo habillé */
.about-teaser-visual {
    position: relative;
    padding: var(--space-xs);
}

.about-img-frame {
    position: relative;
    height: 520px;
    border-radius: var(--radius-xl);
    background: var(--bg-elevated);
    overflow: hidden;
    box-shadow: 
        0 20px 50px rgba(4, 6, 18, 0.75),
        0 0 40px rgba(41, 62, 204, 0.12);
    border: 1px solid rgba(135, 160, 255, 0.18);
}

.about-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: contrast(1.08) saturate(1.05);
    transition: transform 0.8s var(--ease-out);
}

.about-img-frame:hover .about-img {
    transform: scale(1.04);
}

.about-frame-border {
    position: absolute;
    inset: 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: var(--radius-lg);
    pointer-events: none;
    transition: border-color var(--transition-base);
}

.about-img-frame:hover .about-frame-border {
    border-color: rgba(160, 185, 255, 0.28);
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

    .about-specialties-grid {
        grid-template-columns: 1fr;
    }

    .about-teaser-visual {
        height: 250px;
    }
}
</style>
