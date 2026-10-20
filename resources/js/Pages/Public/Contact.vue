<script setup>
import { ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import ScrollReveal from '@/Components/ScrollReveal.vue';
import { PhEnvelopeSimple, PhPhone, PhMapPin, PhInstagramLogo, PhCheckCircle } from '@phosphor-icons/vue';

defineOptions({ layout: PublicLayout });

const props = defineProps({
    settings: Object,
});

const page = usePage();
const sent = ref(false);

const form = useForm({
    name: '',
    email: '',
    subject: '',
    message: '',
    full_name: '', // Honeypot field
});

const submit = () => {
    form.post('/contact', {
        preserveScroll: true,
        onSuccess: () => {
            sent.value = true;
            form.reset();
        },
    });
};
</script>

<template>
    <Head>
        <title>Contact & Réservation | Florian Sanchez Photographe</title>
        <meta name="description" content="Une question, un devis ou un projet photo ? Contactez Florian Sanchez pour échanger sur vos besoins en portrait, reportage ou événementiel." />
        <meta property="og:title" content="Contact & Réservation | Florian Sanchez Photographe" />
        <meta property="og:description" content="Prenez contact avec Florian Sanchez pour discuter de votre prochain shooting photo." />
    </Head>

    <section class="section">
        <div class="container">
            <div class="contact-layout">
                <!-- Left: Info -->
                <ScrollReveal :x="-20" :y="0">
                    <div class="contact-info">
                        <span class="section-eyebrow">Contact</span>
                        <h1>Discutons de votre projet</h1>
                        <p>
                            Vous avez un projet photo en tête ? N'hésitez pas à me contacter,
                            je serai ravi d'en discuter avec vous.
                        </p>

                        <div class="contact-details">
                            <div v-if="settings?.email" class="contact-detail">
                                <span class="contact-icon">
                                    <PhEnvelopeSimple :size="20" weight="fill" />
                                </span>
                                <div>
                                    <div class="contact-detail-label">Email</div>
                                    <a :href="`mailto:${settings.email}`">{{ settings.email }}</a>
                                </div>
                            </div>
                            <div v-if="settings?.phone" class="contact-detail">
                                <span class="contact-icon">
                                    <PhPhone :size="20" weight="fill" />
                                </span>
                                <div>
                                    <div class="contact-detail-label">Téléphone</div>
                                    <a :href="`tel:${settings.phone}`">{{ settings.phone }}</a>
                                </div>
                            </div>
                            <div v-if="settings?.location" class="contact-detail">
                                <span class="contact-icon">
                                    <PhMapPin :size="20" weight="fill" />
                                </span>
                                <div>
                                    <div class="contact-detail-label">Localisation</div>
                                    <span>{{ settings.location }}</span>
                                </div>
                            </div>
                            <div class="contact-detail">
                                <span class="contact-icon">
                                    <PhInstagramLogo :size="20" weight="fill" />
                                </span>
                                <div>
                                    <div class="contact-detail-label">Instagram</div>
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
                        </div>
                    </div>
                </ScrollReveal>

                <!-- Right: Form -->
                <ScrollReveal :x="20" :y="0" :delay="0.15">
                    <div class="contact-form-card card">
                        <Transition name="fade" mode="out-in">
                            <div v-if="sent" class="success-msg" key="success">
                                <div class="success-icon">
                                    <PhCheckCircle :size="32" weight="fill" />
                                </div>
                                <h3>Message envoyé !</h3>
                                <p>Merci pour votre message. Je vous répondrai dans les meilleurs délais.</p>
                                <button class="btn btn-ghost" @click="sent = false">Envoyer un autre message</button>
                            </div>

                            <form v-else @submit.prevent="submit" key="form">
                                <!-- Honeypot field (hidden from humans) -->
                                <div style="display: none !important;" aria-hidden="true">
                                    <input type="text" v-model="form.full_name" tabindex="-1" autocomplete="off" />
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Nom *</label>
                                    <input type="text" v-model="form.name" placeholder="Votre nom" required />
                                    <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Email *</label>
                                    <input type="email" v-model="form.email" placeholder="votre@email.com" required />
                                    <div v-if="form.errors.email" class="form-error">{{ form.errors.email }}</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Sujet</label>
                                    <input type="text" v-model="form.subject" placeholder="Objet de votre message" />
                                    <div v-if="form.errors.subject" class="form-error">{{ form.errors.subject }}</div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Message *</label>
                                    <textarea v-model="form.message" rows="5" placeholder="Décrivez votre projet..." required></textarea>
                                    <div v-if="form.errors.message" class="form-error">{{ form.errors.message }}</div>
                                </div>

                                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;" :disabled="form.processing">
                                    {{ form.processing ? 'Envoi en cours...' : 'Envoyer le message' }}
                                </button>
                            </form>
                        </Transition>
                    </div>
                </ScrollReveal>
            </div>
        </div>
    </section>
</template>

<style scoped>
.contact-layout {
    display: grid;
    grid-template-columns: 1fr 1.2fr;
    gap: var(--space-4xl);
    align-items: flex-start;
    padding-top: var(--space-2xl);
}

.contact-info .section-eyebrow {
    text-align: left;
}

.contact-info h1 {
    font-size: 2.5rem;
    margin-bottom: var(--space-md);
}

.contact-info > p {
    font-size: 1.05rem;
    line-height: 1.8;
    color: var(--text-secondary);
}

.contact-details {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
    margin-top: var(--space-2xl);
}

.contact-detail {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
}

.contact-icon {
    font-size: 1.4rem;
    margin-top: 2px;
}

.contact-detail-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: 2px;
}

.contact-detail a,
.contact-detail span {
    color: var(--text-secondary);
    font-size: 0.95rem;
}

.contact-detail a:hover {
    color: var(--primary-light);
}

.contact-insta-badge {
    display: inline-flex !important;
    align-items: center;
    gap: 7px;
    margin-top: 6px;
    padding: 6px 14px;
    background: linear-gradient(135deg, rgba(157, 78, 221, 0.16), rgba(41, 62, 204, 0.16));
    border: 1px solid rgba(181, 101, 247, 0.35);
    border-radius: var(--radius-full);
    color: #e9d5ff !important;
    font-size: 0.88rem !important;
    font-weight: 500;
    text-decoration: none;
    transition: all var(--transition-fast);
}

.contact-insta-badge:hover {
    background: linear-gradient(135deg, rgba(157, 78, 221, 0.3), rgba(41, 62, 204, 0.3));
    border-color: rgba(200, 140, 255, 0.65);
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(157, 78, 221, 0.28);
}

.insta-badge-arrow {
    font-size: 0.78rem;
    opacity: 0.7;
    transition: transform var(--transition-fast);
}

.contact-insta-badge:hover .insta-badge-arrow {
    transform: translate(2px, -2px);
    opacity: 1;
}

.contact-form-card {
    padding: var(--space-2xl);
}

/* Success */
.success-msg {
    text-align: center;
    padding: var(--space-2xl) 0;
}

.success-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: rgba(16, 185, 129, 0.15);
    color: var(--success);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin: 0 auto var(--space-lg);
}

.success-msg h3 {
    margin-bottom: var(--space-sm);
}

.success-msg p {
    color: var(--text-muted);
    margin-bottom: var(--space-xl);
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
    opacity: 0;
    transform: translateY(10px);
}

.fade-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

@media (max-width: 768px) {
    .contact-layout {
        grid-template-columns: 1fr;
        gap: var(--space-2xl);
    }

    .contact-info h1 {
        font-size: 2rem;
    }
}
</style>
