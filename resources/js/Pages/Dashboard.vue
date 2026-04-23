<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentContacts: Array,
});
</script>

<template>
    <Head title="Tableau de bord" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Tableau de bord</h2>
        </template>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-primary">📸</div>
                <div>
                    <div class="stat-value">{{ stats.projects }}</div>
                    <div class="stat-label">Projets</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-accent">🖼️</div>
                <div>
                    <div class="stat-value">{{ stats.photos }}</div>
                    <div class="stat-label">Photos</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-success">🏷️</div>
                <div>
                    <div class="stat-value">{{ stats.categories }}</div>
                    <div class="stat-label">Catégories</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-warning">✉️</div>
                <div>
                    <div class="stat-value">{{ stats.unreadMessages }}</div>
                    <div class="stat-label">Messages non lus</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card" style="margin-top: var(--space-xl);">
            <h3 style="margin-bottom: var(--space-lg);">Accès rapides</h3>
            <div style="display: flex; gap: var(--space-md); flex-wrap: wrap;">
                <Link href="/admin/projects/create" class="btn btn-primary">+ Nouveau projet</Link>
                <Link href="/admin/categories" class="btn btn-ghost">Gérer les catégories</Link>
                <Link href="/admin/contacts" class="btn btn-ghost">Voir les messages</Link>
                <Link href="/admin/settings" class="btn btn-ghost">Paramètres</Link>
            </div>
        </div>

        <!-- Recent Messages -->
        <div v-if="recentContacts.length > 0" class="card" style="margin-top: var(--space-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
                <h3 style="margin: 0;">Derniers messages</h3>
                <Link href="/admin/contacts" class="btn btn-ghost btn-sm">Tout voir</Link>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Sujet</th>
                        <th>Date</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="contact in recentContacts" :key="contact.id">
                        <td :style="{ fontWeight: contact.is_read ? 400 : 600 }">{{ contact.name }}</td>
                        <td>{{ contact.subject || '—' }}</td>
                        <td>{{ new Date(contact.created_at).toLocaleDateString('fr-FR') }}</td>
                        <td>
                            <span :class="contact.is_read ? 'badge badge-success' : 'badge badge-warning'">
                                {{ contact.is_read ? 'Lu' : 'Non lu' }}
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: var(--space-lg);
}
</style>
