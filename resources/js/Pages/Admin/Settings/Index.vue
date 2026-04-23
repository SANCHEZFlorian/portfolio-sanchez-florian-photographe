<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    settings: Object,
});

const form = useForm({
    site_title: props.settings?.site_title || 'Florian Sanchez Photographie',
    bio_short: props.settings?.bio_short || '',
    bio: props.settings?.bio || '',
    about_text: props.settings?.about_text || '',
    email: props.settings?.email || '',
    phone: props.settings?.phone || '',
    location: props.settings?.location || '',
    instagram_url: props.settings?.instagram_url || '',
    equipment: props.settings?.equipment || '',
});

const save = () => {
    form.post('/admin/settings', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Paramètres" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Paramètres du site</h2>
        </template>

        <form @submit.prevent="save" style="max-width: 800px;">
            <!-- General -->
            <div class="card" style="margin-bottom: var(--space-xl);">
                <h3 style="color: var(--primary-light); margin-bottom: var(--space-lg);">Général</h3>

                <div class="form-group">
                    <label class="form-label">Titre du site</label>
                    <input type="text" v-model="form.site_title" />
                </div>

                <div class="form-group">
                    <label class="form-label">Bio courte (affichée sur l'accueil)</label>
                    <textarea v-model="form.bio_short" rows="2" placeholder="Une phrase résumant votre activité..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Bio (page À propos)</label>
                    <textarea v-model="form.bio" rows="4" placeholder="Votre parcours, votre passion..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Texte À propos (premier paragraphe)</label>
                    <textarea v-model="form.about_text" rows="4" placeholder="Introduction détaillée..."></textarea>
                </div>
            </div>

            <!-- Contact -->
            <div class="card" style="margin-bottom: var(--space-xl);">
                <h3 style="color: var(--primary-light); margin-bottom: var(--space-lg);">Coordonnées</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg);">
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" v-model="form.email" placeholder="votre@email.com" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" v-model="form.phone" placeholder="06 12 34 56 78" />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Localisation</label>
                    <input type="text" v-model="form.location" placeholder="Ex: Lyon, France" />
                </div>

                <div class="form-group">
                    <label class="form-label">Lien Instagram</label>
                    <input type="url" v-model="form.instagram_url" placeholder="https://instagram.com/votre_profil" />
                </div>
            </div>

            <!-- Equipment -->
            <div class="card" style="margin-bottom: var(--space-xl);">
                <h3 style="color: var(--primary-light); margin-bottom: var(--space-lg);">Équipement (optionnel)</h3>

                <div class="form-group">
                    <label class="form-label">Mon matériel</label>
                    <textarea v-model="form.equipment" rows="6" placeholder="Canon EOS R5&#10;Sony 85mm f/1.4&#10;..."></textarea>
                    <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: var(--space-xs);">
                        Laissez vide pour masquer la section sur la page À propos.
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg" :disabled="form.processing">
                {{ form.processing ? 'Sauvegarde...' : 'Sauvegarder les paramètres' }}
            </button>
        </form>
    </AuthenticatedLayout>
</template>
