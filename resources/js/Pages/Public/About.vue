<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import { PhMapPin, PhEnvelopeSimple, PhInstagramLogo } from '@phosphor-icons/vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    settings: Object,
});

const getInstaUsername = (url) => {
    if (!url) return '';
    const parts = url.replace(/\/$/, '').split('/');
    return '@' + parts[parts.length - 1];
};
</script>

<template>
    <Head>
        <title>À propos | Florian Sanchez Photographe</title>
        <meta name="description" content="Découvrez le parcours, l'approche et la sensibilité artistique de Florian Sanchez, photographe professionnel à Toulouse et partout ailleurs." />
        <meta property="og:title" content="À propos | Florian Sanchez Photographe" />
        <meta property="og:description" content="Découvrez le parcours et la démarche artistique de Florian Sanchez, photographe." />
    </Head>

    <section class="section">
        <div class="container">
            <div class="about-layout">
                <!-- Visual side -->
                <ScrollReveal :x="-30" :y="0">
                    <div class="about-visual">
                        <div class="about-visual-card">
                            <img src="/images/florian-sanchez.jpg" alt="Florian Sanchez" class="about-portrait" />
                        </div>
                    </div>
                </ScrollReveal>

                <!-- Text side -->
                <div class="about-text">
                    <ScrollReveal :delay="0.1">
                        <span class="section-eyebrow">À propos</span>
                        <h1>Florian Sanchez</h1>
                        <h2 class="display-text gradient-text">Photographe</h2>
                    </ScrollReveal>

                    <ScrollReveal :delay="0.2">
                        <div class="about-bio">
                            <p>
                                {{ settings?.about_text || "Passionné par la photographie, je capture des instants authentiques qui racontent des histoires. Mon approche mêle rigueur technique, sens du cadrage et sensibilité pour sublimer la faune, la nature et la mécanique." }}
                            </p>
                            <p>
                                {{ settings?.bio || "Spécialisé dans la faune animale, les paysages naturels ainsi que l'univers automobile et moto, je parcours les routes et les grands espaces pour saisir des images fortes, contrastées et vibrantes." }}
                            </p>
                        </div>
                    </ScrollReveal>

                    <ScrollReveal :delay="0.3">
                        <div class="about-details">
                            <div v-if="settings?.location" class="about-detail">
                                <span class="about-detail-icon">
                                    <PhMapPin :size="18" weight="fill" />
                                </span>
                                <span>{{ settings.location }}</span>
                            </div>
                            <div v-if="settings?.email" class="about-detail">
                                <span class="about-detail-icon">
                                    <PhEnvelopeSimple :size="18" weight="fill" />
                                </span>
                                <a :href="`mailto:${settings.email}`">{{ settings.email }}</a>
                            </div>
                            <div class="about-detail">
                                <span class="about-detail-icon">
                                    <PhInstagramLogo :size="18" weight="fill" />
                                </span>
                                <a
                                    :href="settings?.instagram_url || 'https://www.instagram.com/ascorio17/'"
                                    target="_blank"
                                    rel="noopener"
                                    class="contact-insta-badge"
                                >
                                    <PhInstagramLogo :size="15" weight="fill" />
                                    <span>@ascorio17</span>
                                    <span class="insta-badge-arrow">↗</span>
                                </a>
                            </div>
                        </div>
                    </ScrollReveal>

                    <ScrollReveal :delay="0.4">
                        <div class="about-cta">
                            <Link href="/projets" class="btn btn-primary">Voir mes projets</Link>
                            <Link href="/contact" class="btn btn-ghost">Me contacter</Link>
                        </div>
                    </ScrollReveal>
                </div>
            </div>
        </div>
    </section>

    <!-- Equipment / Philosophy -->
    <section class="section" style="background: var(--bg-deep);" v-if="settings?.equipment">
        <div class="container">
            <ScrollReveal>
                <div class="section-header">
                    <span class="section-eyebrow">Équipement</span>
                    <h2>Mon matériel</h2>
                </div>
            </ScrollReveal>
            <ScrollReveal :delay="0.15">
                <div class="card" style="max-width: 700px; margin: 0 auto;">
                    <p style="white-space: pre-line;">{{ settings.equipment }}</p>
                </div>
            </ScrollReveal>
        </div>
    </section>
</template>

<style scoped>
.about-layout {
    display: grid;
    grid-template-columns: 1fr 1.3fr;
    gap: var(--space-4xl);
    align-items: center;
    padding-top: var(--space-2xl);
}

.about-visual-card {
    position: relative;
    width: 100%;
    aspect-ratio: 3/4;
    border-radius: var(--radius-xl);
    overflow: hidden;
    background: var(--surface-color);
    border: 1px solid var(--border-color);
}

.about-portrait {
    width: 100%;
    height: 100%;
    object-fit: cover;
    filter: grayscale(0.1) contrast(1.05);
    transition: transform 0.8s var(--ease-out);
}

.about-visual-card:hover .about-portrait {
    transform: scale(1.05);
}

.about-text .section-eyebrow {
    text-align: left;
}

.about-text h1 {
    font-size: 2.5rem;
    margin-bottom: 0;
}

.about-text h2 {
    font-size: 2rem;
    margin-bottom: var(--space-xl);
}

.about-bio p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: var(--text-secondary);
}

.about-details {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    margin: var(--space-xl) 0;
    padding: var(--space-lg) 0;
    border-top: 1px solid var(--border-subtle);
    border-bottom: 1px solid var(--border-subtle);
}

.about-detail {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    font-size: 0.95rem;
}

.about-detail-icon {
    font-size: 1.2rem;
}

.about-detail a {
    color: var(--text-secondary);
}

.about-detail a:hover {
    color: var(--primary-light);
}

.about-cta {
    display: flex;
    gap: var(--space-md);
    margin-top: var(--space-xl);
}

@media (max-width: 768px) {
    .about-layout {
        grid-template-columns: 1fr;
        gap: var(--space-2xl);
    }

    .about-visual-card {
        aspect-ratio: 16/9;
    }
}
</style>
