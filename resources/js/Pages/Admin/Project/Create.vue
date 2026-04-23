<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    title: '',
    category_id: '',
    description: '',
    instagram_url: '',
    date: '',
    location: '',
    is_featured: false,
    status: 'published',
});

const submit = () => {
    form.post('/admin/projects', {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Nouveau projet" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Nouveau projet</h2>
            <Link href="/admin/projects" class="btn btn-ghost btn-sm">← Retour</Link>
        </template>

        <div class="card" style="max-width: 700px;">
            <form @submit.prevent="submit">
                <div class="form-group">
                    <label class="form-label">Titre *</label>
                    <input type="text" v-model="form.title" required placeholder="Ex: Shooting avec Antoine & sa BMW" />
                    <div v-if="form.errors.title" class="form-error">{{ form.errors.title }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Catégorie *</label>
                    <select v-model="form.category_id" required>
                        <option value="" disabled>Sélectionner une catégorie</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                    <div v-if="form.errors.category_id" class="form-error">{{ form.errors.category_id }}</div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg);">
                    <div class="form-group">
                        <label class="form-label">Date du shooting</label>
                        <input type="date" v-model="form.date" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lieu</label>
                        <input type="text" v-model="form.location" placeholder="Ex: Paris, Lyon..." />
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Lien Instagram</label>
                    <input type="url" v-model="form.instagram_url" placeholder="https://instagram.com/..." />
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea v-model="form.description" rows="4" placeholder="Décrivez le shooting..."></textarea>
                </div>

                <div style="display: flex; gap: var(--space-xl); margin-bottom: var(--space-xl);">
                    <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer; color: var(--text-secondary);">
                        <input type="checkbox" v-model="form.is_featured" style="width: auto; accent-color: var(--primary);" />
                        Mettre en avant sur l'accueil
                    </label>
                    <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer; color: var(--text-secondary);">
                        <select v-model="form.status" style="width: auto; padding: 0.4rem 2rem 0.4rem 0.6rem;">
                            <option value="published">Publié</option>
                            <option value="draft">Brouillon</option>
                        </select>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;" :disabled="form.processing">
                    {{ form.processing ? 'Création...' : 'Créer le projet et ajouter des photos' }}
                </button>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
