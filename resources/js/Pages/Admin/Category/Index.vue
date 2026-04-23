<script setup>
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminModal from '@/Components/AdminModal.vue';

const props = defineProps({
    categories: Array,
});

const showModal = ref(false);
const editing = ref(null);

const form = useForm({
    name: '',
    description: '',
});

const openCreate = () => {
    editing.value = null;
    form.name = '';
    form.description = '';
    showModal.value = true;
};

const openEdit = (cat) => {
    editing.value = cat;
    form.name = cat.name;
    form.description = cat.description || '';
    showModal.value = true;
};

const save = () => {
    if (editing.value) {
        form.put(`/admin/categories/${editing.value.id}`, {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        form.post('/admin/categories', {
            preserveScroll: true,
            onSuccess: () => { showModal.value = false; form.reset(); },
        });
    }
};

const deleteCategory = (cat) => {
    if (confirm(`Supprimer la catégorie "${cat.name}" ? Les projets ne seront pas supprimés, mais ne seront plus liés à cette catégorie.`)) {
        router.delete(`/admin/categories/${cat.id}`, {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <Head title="Catégories" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Catégories</h2>
            <button class="btn btn-primary" @click="openCreate">+ Nouvelle catégorie</button>
        </template>

        <div class="card">
            <table v-if="categories.length > 0">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Slug</th>
                        <th>Projets</th>
                        <th>Photos</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="cat in categories" :key="cat.id">
                        <td style="font-weight: 500;">{{ cat.name }}</td>
                        <td><span class="badge badge-primary">{{ cat.slug }}</span></td>
                        <td>{{ cat.projects_count ?? 0 }}</td>
                        <td>{{ cat.photos_count ?? 0 }}</td>
                        <td>
                            <div style="display: flex; gap: var(--space-sm);">
                                <button class="btn btn-ghost btn-sm" @click="openEdit(cat)">Modifier</button>
                                <button class="btn btn-danger btn-sm" @click="deleteCategory(cat)">Supprimer</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-else class="empty-state">
                <div class="empty-state-icon">🏷️</div>
                <div class="empty-state-title">Aucune catégorie</div>
                <div class="empty-state-text">Créez votre première catégorie pour organiser vos projets.</div>
                <button class="btn btn-primary" @click="openCreate" style="margin-top: var(--space-lg);">+ Créer une catégorie</button>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <AdminModal :show="showModal" @close="showModal = false" :title="editing ? 'Modifier la catégorie' : 'Nouvelle catégorie'">
            <form @submit.prevent="save">
                <div class="form-group">
                    <label class="form-label">Nom *</label>
                    <input type="text" v-model="form.name" required placeholder="Ex: Portrait, Lifestyle, Urbain..." />
                    <div v-if="form.errors.name" class="form-error">{{ form.errors.name }}</div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea v-model="form.description" rows="3" placeholder="Description optionnelle..."></textarea>
                    <div v-if="form.errors.description" class="form-error">{{ form.errors.description }}</div>
                </div>

                <div style="display: flex; gap: var(--space-sm); justify-content: flex-end;">
                    <button type="button" class="btn btn-ghost" @click="showModal = false">Annuler</button>
                    <button type="submit" class="btn btn-primary" :disabled="form.processing">
                        {{ form.processing ? 'En cours...' : (editing ? 'Mettre à jour' : 'Créer') }}
                    </button>
                </div>
            </form>
        </AdminModal>
    </AuthenticatedLayout>
</template>
