<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';

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
    <Head title="À propos" />

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
                                {{ settings?.about_text || "Passionné par la photographie depuis toujours, je capture les moments qui racontent des histoires uniques. Mon approche mêle créativité, technique et sensibilité pour créer des images qui marquent les esprits." }}
                            </p>
                            <p>
                                {{ settings?.bio || "Spécialisé dans le portrait, la photo lifestyle et les shootings créatifs, je travaille avec des particuliers et des professionnels pour donner vie à leurs projets visuels." }}
                            </p>
                        </div>
                    </ScrollReveal>

                    <ScrollReveal :delay="0.3">
                        <div class="about-details">
                            <div v-if="settings?.location" class="about-detail">
                                <span class="about-detail-icon">📍</span>
                                <span>{{ settings.location }}</span>
                            </div>
                            <div v-if="settings?.email" class="about-detail">
                                <span class="about-detail-icon">✉️</span>
                                <a :href="`mailto:${settings.email}`">{{ settings.email }}</a>
                            </div>
                            <div v-if="settings?.instagram_url" class="about-detail">
                                <span class="about-detail-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                                    </svg>
                                </span>
                                <a :href="settings.instagram_url" target="_blank" rel="noopener">{{ getInstaUsername(settings.instagram_url) }}</a>
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
