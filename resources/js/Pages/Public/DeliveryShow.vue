<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import MasonryGrid from '@/Components/MasonryGrid.vue';
import PhotoLightbox from '@/Components/PhotoLightbox.vue';
import {
    PhCamera,
    PhMapPin,
    PhCalendarBlank,
    PhDownloadSimple,
    PhLockKey,
    PhHardDrives,
    PhQuotes,
} from '@phosphor-icons/vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    project: Object,
    totalSizeFormatted: String,
    settings: Object,
});

const activePhoto = ref(null);

const displayTitle = props.project.delivery_title || props.project.title;

const defaultMessage = `Bonjour ! Je suis très heureux de vous remettre les clichés de notre shooting. Prenez le temps de parcourir la galerie en grand format ci-dessous. Vous pouvez télécharger l'archive complète en haute définition originale ou enregistrer vos photos préférées une par une.`;
</script>

<template>
    <Head>
        <title>{{ displayTitle }} | Galerie Privée — Florian Sanchez</title>
        <meta name="robots" content="noindex, nofollow" />
        <meta name="description" :content="`Galerie privée de remise des photos pour ${displayTitle} par Florian Sanchez Photographe.`" />
    </Head>

    <div class="delivery-page">
        <!-- En-tête Client Premium -->
        <section class="delivery-hero">
            <div class="container">
                <ScrollReveal>
                    <div class="delivery-badge-wrapper">
                        <span class="delivery-badge">
                            <PhLockKey :size="14" weight="fill" />
                            <span>Espace Client Privé</span>
                        </span>
                        <span v-if="project.category" class="delivery-category">
                            {{ project.category.name }}
                        </span>
                    </div>
                </ScrollReveal>

                <ScrollReveal :delay="0.1">
                    <h1 class="delivery-title">{{ displayTitle }}</h1>

                    <div class="delivery-meta-strip">
                        <span v-if="project.location" class="meta-item">
                            <PhMapPin :size="16" weight="fill" />
                            <span>{{ project.location }}</span>
                        </span>
                        <span v-if="project.date" class="meta-item">
                            <PhCalendarBlank :size="16" weight="fill" />
                            <span>{{ new Date(project.date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
                        </span>
                        <span class="meta-item">
                            <PhCamera :size="16" weight="fill" />
                            <span>{{ project.photos.length }} photos</span>
                        </span>
                        <span v-if="totalSizeFormatted" class="meta-item">
                            <PhHardDrives :size="16" weight="fill" />
                            <span>~ {{ totalSizeFormatted }}</span>
                        </span>
                    </div>
                </ScrollReveal>

                <!-- Mot personnalisé du photographe -->
                <ScrollReveal :delay="0.15">
                    <div class="delivery-message-card">
                        <div class="message-quote-icon">
                            <PhQuotes :size="28" weight="fill" />
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-author">Florian Sanchez</span>
                                <span class="message-role">Photographe</span>
                            </div>
                            <p class="message-text">
                                {{ project.delivery_message || defaultMessage }}
                            </p>
                        </div>
                    </div>
                </ScrollReveal>

                <!-- Bouton d'action principal : Télécharger l'archive ZIP -->
                <ScrollReveal :delay="0.2">
                    <div class="delivery-cta-container">
                        <a
                            :href="`/livraison/${project.delivery_token}/telecharger-zip`"
                            class="delivery-download-btn"
                            download
                        >
                            <div class="btn-icon-wrapper">
                                <PhDownloadSimple :size="24" weight="bold" />
                            </div>
                            <div class="btn-text-content">
                                <span class="btn-primary-label">Tout télécharger en Haute Définition</span>
                                <span class="btn-secondary-label">Archive ZIP complète • {{ totalSizeFormatted }}</span>
                            </div>
                        </a>
                        <p class="cta-hint">
                            💡 Astuce : Cliquez sur n'importe quel cliché pour l'afficher en plein écran et le télécharger individuellement.
                        </p>
                    </div>
                </ScrollReveal>
            </div>
        </section>

        <!-- Galerie des clichés -->
        <section class="section-sm delivery-gallery-section">
            <div class="container container-wide">
                <MasonryGrid
                    v-if="project.photos.length > 0"
                    :photos="project.photos"
                    @photoClick="(p) => activePhoto = p"
                />

                <div v-else class="empty-state">
                    <div class="empty-state-icon">
                        <PhCamera :size="48" weight="light" />
                    </div>
                    <div class="empty-state-title">Préparation des clichés en cours</div>
                    <div class="empty-state-text">Les photos haute définition de ce shooting sont en cours d'intégration.</div>
                </div>

                <!-- Lightbox avec téléchargement HD unitaire -->
                <PhotoLightbox
                    :photos="project.photos"
                    :activePhoto="activePhoto"
                    :deliveryToken="project.delivery_token"
                    @close="activePhoto = null"
                    @next="(p) => activePhoto = p"
                    @prev="(p) => activePhoto = p"
                />
            </div>
        </section>
    </div>
</template>

<style scoped>
.delivery-page {
    position: relative;
    padding-bottom: var(--space-3xl);
}

.delivery-hero {
    padding-top: var(--space-3xl);
    padding-bottom: var(--space-2xl);
    text-align: center;
}

.delivery-hero .container {
    max-width: 900px;
    margin: 0 auto;
}

.delivery-badge-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    margin-bottom: var(--space-md);
}

.delivery-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.35rem 0.85rem;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    background: rgba(157, 78, 221, 0.15);
    color: var(--accent-light);
    border: 1px solid rgba(157, 78, 221, 0.35);
    border-radius: var(--radius-full);
    backdrop-filter: blur(8px);
}

.delivery-category {
    font-size: 0.85rem;
    color: var(--text-muted);
    padding: 0.35rem 0.75rem;
    background: rgba(255, 255, 255, 0.04);
    border-radius: var(--radius-full);
}

.delivery-title {
    font-size: clamp(2.2rem, 5vw, 3.8rem);
    margin-bottom: var(--space-md);
    line-height: 1.15;
}

.delivery-meta-strip {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    align-items: center;
    gap: var(--space-lg);
    font-size: 0.95rem;
    color: var(--text-muted);
    margin-bottom: var(--space-xl);
}

.meta-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

/* Carte du mot personnalisé */
.delivery-message-card {
    position: relative;
    background: linear-gradient(135deg, rgba(25, 26, 45, 0.7) 0%, rgba(15, 16, 30, 0.8) 100%);
    border: 1px solid rgba(157, 78, 221, 0.25);
    border-radius: 16px;
    padding: var(--space-xl);
    text-align: left;
    margin-bottom: var(--space-2xl);
    backdrop-filter: blur(16px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
}

.message-quote-icon {
    position: absolute;
    top: -14px;
    left: 28px;
    color: var(--accent-light);
    background: var(--bg-deep, #0a0b16);
    padding: 0 8px;
    border-radius: 6px;
}

.message-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.message-header {
    display: flex;
    align-items: center;
    gap: 8px;
}

.message-author {
    font-family: 'Playfair Display', serif;
    font-size: 1.15rem;
    font-weight: 600;
    color: #fff;
}

.message-role {
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--accent-light);
    background: rgba(157, 78, 221, 0.15);
    padding: 2px 8px;
    border-radius: var(--radius-full);
}

.message-text {
    font-size: 1.02rem;
    line-height: 1.7;
    color: var(--text-secondary);
    white-space: pre-line;
    margin: 0;
}

.message-footer {
    margin-top: var(--space-xs);
}

.quality-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    color: #34d399;
    background: rgba(16, 185, 129, 0.1);
    border: 1px solid rgba(16, 185, 129, 0.25);
    padding: 4px 10px;
    border-radius: var(--radius-full);
}

/* Bouton Télécharger */
.delivery-cta-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: var(--space-sm);
    margin-bottom: var(--space-xl);
}

.delivery-download-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-md);
    background: linear-gradient(135deg, #7928ca 0%, #4338ca 100%);
    color: #fff;
    padding: 1rem 2rem;
    border-radius: var(--radius-full);
    text-decoration: none;
    box-shadow: 0 8px 25px rgba(121, 40, 202, 0.4);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    border: 1px solid rgba(255, 255, 255, 0.15);
}

.delivery-download-btn:hover {
    transform: translateY(-3px) scale(1.02);
    box-shadow: 0 12px 35px rgba(121, 40, 202, 0.6);
    color: #fff;
}

.btn-icon-wrapper {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    backdrop-filter: blur(4px);
}

.btn-text-content {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
}

.btn-primary-label {
    font-size: 1.1rem;
    font-weight: 600;
    letter-spacing: 0.3px;
}

.btn-secondary-label {
    font-size: 0.8rem;
    opacity: 0.85;
    font-weight: 400;
}

.cta-hint {
    font-size: 0.85rem;
    color: var(--text-muted);
    margin: 0;
}

@media (max-width: 640px) {
    .delivery-download-btn {
        width: 100%;
        padding: 0.85rem 1.25rem;
    }
    .btn-primary-label {
        font-size: 0.95rem;
    }
    .btn-secondary-label {
        font-size: 0.75rem;
    }
}
</style>
