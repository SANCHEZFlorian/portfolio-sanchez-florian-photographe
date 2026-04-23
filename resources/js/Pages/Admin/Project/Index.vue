<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    projects: Array,
});
</script>

<template>
    <Head title="Projets" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Projets</h2>
            <Link href="/admin/projects/create" class="btn btn-primary">+ Nouveau projet</Link>
        </template>

        <div class="card" v-if="projects.length > 0">
            <table>
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Catégorie</th>
                        <th>Photos</th>
                        <th>Statut</th>
                        <th>En avant</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="project in projects" :key="project.id">
                        <td style="font-weight: 500;">{{ project.title }}</td>
                        <td>
                            <span v-if="project.category" class="badge badge-primary">{{ project.category.name }}</span>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td>{{ project.photos_count }}</td>
                        <td>
                            <span :class="project.status === 'published' ? 'badge badge-success' : 'badge badge-warning'">
                                {{ project.status === 'published' ? 'Publié' : 'Brouillon' }}
                            </span>
                        </td>
                        <td>
                            <span v-if="project.is_featured" class="badge badge-accent">★</span>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td>
                            <div style="display: flex; gap: var(--space-sm);">
                                <Link :href="`/admin/projects/${project.id}/edit`" class="btn btn-ghost btn-sm">Éditer</Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="empty-state">
            <div class="empty-state-icon">📸</div>
            <div class="empty-state-title">Aucun projet</div>
            <div class="empty-state-text">Créez votre premier projet pour commencer à organiser vos shootings.</div>
            <Link href="/admin/projects/create" class="btn btn-primary" style="margin-top: var(--space-lg);">+ Créer un projet</Link>
        </div>
    </AuthenticatedLayout>
</template>
