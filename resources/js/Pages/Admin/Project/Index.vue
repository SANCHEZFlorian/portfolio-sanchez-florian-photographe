<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { PhPlus, PhCamera, PhPencilSimple, PhStar } from '@phosphor-icons/vue';

const props = defineProps({
    projects: Array,
});
</script>

<template>
    <Head title="Projets" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Projets</h2>
            <Link href="/admin/projects/create" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 6px;">
                <PhPlus :size="16" weight="bold" />
                <span>Nouveau projet</span>
            </Link>
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
                            <span v-if="project.is_featured" class="badge badge-accent" style="display: inline-flex; align-items: center; gap: 4px;">
                                <PhStar :size="12" weight="fill" />
                                <span>Vedette</span>
                            </span>
                            <span v-else class="text-muted">—</span>
                        </td>
                        <td>
                            <div style="display: flex; gap: var(--space-sm);">
                                <Link :href="`/admin/projects/${project.id}/edit`" class="btn btn-ghost btn-sm" style="display: inline-flex; align-items: center; gap: 4px;">
                                    <PhPencilSimple :size="14" />
                                    <span>Éditer</span>
                                </Link>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="empty-state">
            <div class="empty-state-icon" style="display: flex; justify-content: center;">
                <PhCamera :size="54" weight="thin" class="text-muted" />
            </div>
            <div class="empty-state-title">Aucun projet</div>
            <div class="empty-state-text">Créez votre premier projet pour commencer à organiser vos shootings.</div>
            <Link href="/admin/projects/create" class="btn btn-primary" style="margin-top: var(--space-lg); display: inline-flex; align-items: center; gap: 6px;">
                <PhPlus :size="16" weight="bold" />
                <span>Créer un projet</span>
            </Link>
        </div>
    </AuthenticatedLayout>
</template>
