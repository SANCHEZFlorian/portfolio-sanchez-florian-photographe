<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import {
    PhLockKey,
    PhKey,
    PhEye,
    PhEyeSlash,
    PhArrowRight,
    PhShieldCheck,
} from '@phosphor-icons/vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    projectTitle: String,
    token: String,
});

const showPassword = ref(false);

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(`/livraison/${props.token}/deverrouiller`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head>
        <title>Accès Privé | {{ projectTitle }} — Florian Sanchez</title>
        <meta name="robots" content="noindex, nofollow" />
    </Head>

    <div class="password-page">
        <div class="container">
            <ScrollReveal>
                <div class="password-card">
                    <!-- Icône Cadenas avec lueur -->
                    <div class="lock-icon-halo">
                        <PhLockKey :size="36" weight="fill" class="lock-icon" />
                    </div>

                    <span class="lock-badge">
                        <PhShieldCheck :size="14" weight="fill" />
                        <span>Espace Protégé</span>
                    </span>

                    <h1 class="password-title">{{ projectTitle }}</h1>
                    <p class="password-subtitle">
                        Ce shooting privé est sécurisé par un mot de passe. Veuillez saisir le code d'accès qui vous a été transmis pour accéder à vos clichés en haute définition.
                    </p>

                    <form @submit.prevent="submit" class="password-form">
                        <div class="input-wrapper" :class="{ 'has-error': form.errors.password }">
                            <PhKey :size="20" weight="duotone" class="input-icon-left" />
                            <input
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                placeholder="Entrez votre mot de passe"
                                required
                                autofocus
                                autocomplete="current-password"
                            />
                            <button
                                type="button"
                                class="toggle-password-btn"
                                @click="showPassword = !showPassword"
                                :title="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                            >
                                <component :is="showPassword ? PhEyeSlash : PhEye" :size="18" weight="bold" />
                            </button>
                        </div>

                        <div v-if="form.errors.password" class="error-message">
                            <span>⚠️ {{ form.errors.password }}</span>
                        </div>

                        <button
                            type="submit"
                            class="unlock-btn"
                            :disabled="form.processing || !form.password.trim()"
                        >
                            <span>{{ form.processing ? 'Vérification...' : 'Accéder à la galerie' }}</span>
                            <PhArrowRight :size="18" weight="bold" />
                        </button>
                    </form>

                    <div class="password-footer">
                        <span>Photographies Florian Sanchez • Remise privée</span>
                    </div>
                </div>
            </ScrollReveal>
        </div>
    </div>
</template>

<style scoped>
.password-page {
    min-height: calc(100vh - 160px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-3xl) var(--space-md);
}

.password-page .container {
    max-width: 520px;
    margin: 0 auto;
}

.password-card {
    background: linear-gradient(145deg, rgba(25, 26, 45, 0.85) 0%, rgba(15, 16, 30, 0.95) 100%);
    border: 1px solid rgba(157, 78, 221, 0.3);
    border-radius: 20px;
    padding: var(--space-2xl) var(--space-xl);
    text-align: center;
    backdrop-filter: blur(20px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.45), 0 0 40px rgba(157, 78, 221, 0.12);
}

.lock-icon-halo {
    width: 72px;
    height: 72px;
    margin: 0 auto var(--space-md);
    border-radius: 50%;
    background: radial-gradient(circle, rgba(157, 78, 221, 0.25) 0%, rgba(157, 78, 221, 0.05) 70%, transparent 100%);
    border: 1px solid rgba(157, 78, 221, 0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 0 25px rgba(157, 78, 221, 0.3);
}

.lock-icon {
    color: var(--accent-light, #c084fc);
    filter: drop-shadow(0 0 8px rgba(157, 78, 221, 0.6));
}

.lock-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 0.3rem 0.8rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--accent-light, #c084fc);
    background: rgba(157, 78, 221, 0.12);
    border: 1px solid rgba(157, 78, 221, 0.3);
    border-radius: var(--radius-full);
    margin-bottom: var(--space-md);
}

.password-title {
    font-family: 'Playfair Display', serif;
    font-size: clamp(1.6rem, 3.5vw, 2.2rem);
    margin-bottom: var(--space-sm);
    color: #fff;
    line-height: 1.2;
}

.password-subtitle {
    font-size: 0.95rem;
    color: var(--text-muted, #94a3b8);
    line-height: 1.6;
    margin-bottom: var(--space-xl);
}

.password-form {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    border-radius: 12px;
    background: rgba(0, 0, 0, 0.4);
    border: 1px solid rgba(255, 255, 255, 0.12);
    transition: all 0.25s ease;
}

.input-wrapper:focus-within {
    border-color: var(--accent-light, #c084fc);
    box-shadow: 0 0 0 3px rgba(157, 78, 221, 0.25);
}

.input-wrapper.has-error {
    border-color: #ef4444;
}

.input-icon-left {
    position: absolute;
    left: 14px;
    color: var(--text-muted, #94a3b8);
    pointer-events: none;
}

.input-wrapper input {
    width: 100%;
    padding: 0.9rem 42px 0.9rem 44px;
    background: transparent;
    border: none;
    color: #fff;
    font-size: 1rem;
    outline: none;
}

.input-wrapper input::placeholder {
    color: rgba(255, 255, 255, 0.35);
}

.toggle-password-btn {
    position: absolute;
    right: 12px;
    background: transparent;
    border: none;
    color: var(--text-muted, #94a3b8);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    border-radius: 4px;
    transition: color 0.2s;
}

.toggle-password-btn:hover {
    color: #fff;
}

.error-message {
    padding: 0.6rem 0.9rem;
    border-radius: 8px;
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.3);
    color: #fca5a5;
    font-size: 0.85rem;
    text-align: left;
}

.unlock-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: linear-gradient(135deg, #7928ca 0%, #4338ca 100%);
    color: #fff;
    padding: 0.95rem var(--space-lg);
    border-radius: var(--radius-full, 9999px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    box-shadow: 0 6px 20px rgba(121, 40, 202, 0.35);
}

.unlock-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(121, 40, 202, 0.5);
}

.unlock-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.password-footer {
    margin-top: var(--space-xl);
    font-size: 0.8rem;
    color: var(--text-muted, #94a3b8);
    opacity: 0.7;
}
</style>
