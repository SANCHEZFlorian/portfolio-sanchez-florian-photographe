<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import {
    PhAperture,
    PhCameraSlash,
    PhLockKey,
    PhWarningCircle,
    PhClockCounterClockwise,
    PhWrench,
    PhGauge,
    PhHouse,
    PhImages,
    PhArrowLeft,
    PhArrowClockwise,
} from '@phosphor-icons/vue';

const props = defineProps({
    status: {
        type: Number,
        default: 404,
    },
    message: {
        type: String,
        default: '',
    },
});

const errorConfig = computed(() => {
    const map = {
        401: {
            code: '401',
            hud: 'AUTH_REQUIRED // F/2.8 • ISO 100',
            subtitle: 'Identification requise',
            title: 'Accès non authentifié',
            description: 'Veuillez vous connecter pour accéder à cet espace privé du studio.',
            icon: PhLockKey,
            accent: '#6366f1',
        },
        403: {
            code: '403',
            hud: 'PRIVATE_DARKROOM // F/4.0 • ISO 200',
            subtitle: 'Zone réservée',
            title: 'Chambre noire privée',
            description: "Vous ne disposez pas des autorisations nécessaires pour accéder à cette partie du portfolio.",
            icon: PhLockKey,
            accent: '#a855f7',
        },
        404: {
            code: '404',
            hud: 'OUT_OF_FOCUS // F/1.4 • 1/1000s • ISO 400',
            subtitle: 'Sujet hors cadre',
            title: 'Cliché introuvable',
            description: "La page ou la série photographique que vous cherchez est hors champ, a été déplacée ou n'existe plus.",
            icon: PhCameraSlash,
            accent: '#6366f1',
        },
        419: {
            code: '419',
            hud: 'LONG_EXPOSURE_TIMEOUT // BULB • ISO 100',
            subtitle: 'Pose longue expirée',
            title: 'Session expirée',
            description: 'Votre session a expiré par mesure de sécurité. Rafraîchissez simplement la page pour reprendre.',
            icon: PhClockCounterClockwise,
            accent: '#8b5cf6',
        },
        429: {
            code: '429',
            hud: 'BURST_BUFFER_FULL // 20 FPS • RAW',
            subtitle: 'Mode rafale saturé',
            title: 'Trop de requêtes',
            description: 'Vous avez envoyé trop de requêtes en peu de temps. Patientez quelques secondes avant de continuer.',
            icon: PhGauge,
            accent: '#ec4899',
        },
        500: {
            code: '500',
            hud: 'SHUTTER_FAULT // ERR 500 • RAW',
            subtitle: 'Incident technique',
            title: 'Imprévu en chambre noire',
            description: 'Une erreur interne est survenue sur le serveur. Nos objectifs refont la mise au point, réessayez dans un instant.',
            icon: PhWarningCircle,
            accent: '#ef4444',
        },
        503: {
            code: '503',
            hud: 'STUDIO_MAINTENANCE // CALIBRATION',
            subtitle: 'Maintenance en cours',
            title: 'Développement en cours',
            description: 'Le portfolio est actuellement en cours de mise à jour ou de maintenance. Nous revenons très vite avec de nouveaux clichés.',
            icon: PhWrench,
            accent: '#a855f7',
        },
    };

    return map[props.status] || {
        code: String(props.status || 500),
        hud: `SYS_ERR_${props.status || 500} // MANUAL_MODE`,
        subtitle: 'Erreur inattendue',
        title: 'Mise au point interrompue',
        description: props.message || 'Une erreur inattendue est survenue lors du chargement de cette page.',
        icon: PhAperture,
        accent: '#6366f1',
    };
});

const goBack = () => {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.location.href = '/';
    }
};

const reloadPage = () => {
    window.location.reload();
};
</script>

<template>
    <Head :title="`${errorConfig.code} — ${errorConfig.title}`" />

    <PublicLayout>
        <section class="error-section">
            <div class="error-viewfinder-card">
                <!-- Coins de viseur optique -->
                <span class="vf-corner vf-tl"></span>
                <span class="vf-corner vf-tr"></span>
                <span class="vf-corner vf-bl"></span>
                <span class="vf-corner vf-br"></span>

                <!-- Barre supérieure HUD Appareil Photo -->
                <div class="error-hud-top">
                    <div class="hud-indicator">
                        <span class="hud-rec-dot"></span>
                        <span class="hud-code-text">{{ errorConfig.hud }}</span>
                    </div>
                    <div class="hud-meter">
                        <span>-2</span>
                        <span>·</span>
                        <span>-1</span>
                        <span>·</span>
                        <span class="hud-zero">0</span>
                        <span>·</span>
                        <span>+1</span>
                        <span>·</span>
                        <span>+2</span>
                    </div>
                </div>

                <!-- Centre : Diaphragme lumineux & Code d'erreur -->
                <div class="error-center">
                    <div class="error-aperture-stage">
                        <div class="aperture-halo" :style="{ '--err-accent': errorConfig.accent }"></div>
                        <div class="aperture-ring-outer">
                            <PhAperture :size="150" weight="thin" class="aperture-bg-icon" />
                        </div>
                        <div class="error-icon-badge">
                            <component :is="errorConfig.icon" :size="38" weight="duotone" />
                        </div>
                    </div>

                    <div class="error-code-display">
                        {{ errorConfig.code }}
                    </div>

                    <div class="error-subtitle">
                        {{ errorConfig.subtitle }}
                    </div>

                    <h1 class="error-title">
                        {{ errorConfig.title }}
                    </h1>

                    <p class="error-description">
                        {{ errorConfig.description }}
                    </p>

                    <!-- Actions -->
                    <div class="error-actions">
                        <Link href="/" class="btn btn-primary error-btn">
                            <PhHouse :size="18" weight="bold" />
                            <span>Retour à l'accueil</span>
                        </Link>

                        <button
                            v-if="status === 419 || status === 503 || status === 500"
                            type="button"
                            class="btn btn-accent error-btn"
                            @click="reloadPage"
                        >
                            <PhArrowClockwise :size="18" weight="bold" />
                            <span>Rafraîchir la page</span>
                        </button>

                        <Link
                            v-else
                            href="/projets"
                            class="btn btn-accent error-btn"
                        >
                            <PhImages :size="18" weight="bold" />
                            <span>Découvrir les projets</span>
                        </Link>

                        <button
                            type="button"
                            class="btn btn-secondary error-btn"
                            @click="goBack"
                        >
                            <PhArrowLeft :size="17" weight="bold" />
                            <span>Page précédente</span>
                        </button>
                    </div>
                </div>

                <!-- Barre inférieure HUD -->
                <div class="error-hud-bottom">
                    <span>FLORIAN SANCHEZ PHOTOGRAPHIE</span>
                    <span class="hud-focus-box">[ AF-C • {{ errorConfig.code }} ]</span>
                    <span>RAW • 3:2</span>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
.error-section {
    min-height: calc(100vh - var(--nav-height) - 90px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-2xl) var(--space-lg);
    position: relative;
    z-index: 2;
}

.error-viewfinder-card {
    position: relative;
    width: 100%;
    max-width: 760px;
    padding: 2.5rem 2.75rem;
    background: rgba(10, 14, 24, 0.72);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: var(--radius-xl);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.65), inset 0 1px 0 rgba(255, 255, 255, 0.06);
    overflow: hidden;
}

/* Coins du viseur reflex */
.vf-corner {
    position: absolute;
    width: 24px;
    height: 24px;
    border-color: rgba(129, 140, 248, 0.45);
    border-style: solid;
    pointer-events: none;
}

.vf-tl {
    top: 16px;
    left: 16px;
    border-width: 2px 0 0 2px;
    border-top-left-radius: 4px;
}

.vf-tr {
    top: 16px;
    right: 16px;
    border-width: 2px 2px 0 0;
    border-top-right-radius: 4px;
}

.vf-bl {
    bottom: 16px;
    left: 16px;
    border-width: 0 0 2px 2px;
    border-bottom-left-radius: 4px;
}

.vf-br {
    bottom: 16px;
    right: 16px;
    border-width: 0 2px 2px 0;
    border-bottom-right-radius: 4px;
}

/* HUD Supérieur */
.error-hud-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.72rem;
    letter-spacing: 0.12em;
    color: rgba(148, 163, 184, 0.75);
    margin-bottom: var(--space-xl);
    padding-bottom: var(--space-sm);
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.hud-indicator {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.hud-rec-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ef4444;
    box-shadow: 0 0 10px #ef4444;
    animation: recPulse 2s infinite ease-in-out;
}

@keyframes recPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.35; transform: scale(0.85); }
}

.hud-meter {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: rgba(148, 163, 184, 0.6);
}

.hud-zero {
    color: #a5b4fc;
    font-weight: 700;
}

/* Zone centrale */
.error-center {
    text-align: center;
    padding: var(--space-md) 0 var(--space-xl);
}

.error-aperture-stage {
    position: relative;
    width: 140px;
    height: 140px;
    margin: 0 auto var(--space-md);
    display: flex;
    align-items: center;
    justify-content: center;
}

.aperture-halo {
    position: absolute;
    inset: -10px;
    border-radius: 50%;
    background: radial-gradient(circle, var(--err-accent, #6366f1) 0%, transparent 70%);
    opacity: 0.25;
    filter: blur(18px);
    animation: haloBreathe 4s infinite ease-in-out;
}

@keyframes haloBreathe {
    0%, 100% { transform: scale(0.95); opacity: 0.22; }
    50% { transform: scale(1.1); opacity: 0.38; }
}

.aperture-ring-outer {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(129, 140, 248, 0.16);
    animation: slowSpin 28s linear infinite;
}

@keyframes slowSpin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.error-icon-badge {
    position: relative;
    z-index: 2;
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: rgba(15, 23, 42, 0.9);
    border: 1px solid rgba(129, 140, 248, 0.35);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #c7d2fe;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5), inset 0 0 15px rgba(99, 102, 241, 0.2);
}

.error-code-display {
    font-family: var(--font-display, 'Playfair Display', serif);
    font-size: clamp(4.2rem, 9vw, 6.5rem);
    font-weight: 600;
    line-height: 1;
    letter-spacing: -0.02em;
    background: linear-gradient(135deg, #ffffff 0%, #c7d2fe 50%, #c084fc 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    margin-bottom: 0.35rem;
    filter: drop-shadow(0 8px 24px rgba(99, 102, 241, 0.25));
}

.error-subtitle {
    font-size: 0.78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.24em;
    color: var(--primary-light, #818cf8);
    margin-bottom: var(--space-sm);
}

.error-title {
    font-family: var(--font-display, 'Playfair Display', serif);
    font-size: clamp(1.75rem, 3.5vw, 2.4rem);
    font-weight: 500;
    color: #f8fafc;
    margin: 0 0 var(--space-md);
}

.error-description {
    max-width: 520px;
    margin: 0 auto var(--space-xl);
    font-size: 1rem;
    line-height: 1.7;
    color: var(--text-secondary, #cbd5e1);
}

.error-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 12px;
}

.error-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

/* HUD Inférieur */
.error-hud-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.7rem;
    letter-spacing: 0.14em;
    color: rgba(148, 163, 184, 0.6);
    padding-top: var(--space-sm);
    border-top: 1px solid rgba(255, 255, 255, 0.06);
}

.hud-focus-box {
    color: rgba(165, 180, 252, 0.85);
}

@media (max-width: 640px) {
    .error-viewfinder-card {
        padding: 1.75rem 1.25rem;
    }

    .error-hud-top,
    .error-hud-bottom {
        justify-content: center;
        text-align: center;
    }

    .hud-meter {
        display: none;
    }

    .error-actions {
        flex-direction: column;
        width: 100%;
    }

    .error-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>
