<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    PhCamera,
    PhImages,
    PhTag,
    PhEnvelopeSimple,
    PhPlus,
    PhSliders,
    PhChatsCircle,
    PhGear,
    PhArrowRight,
} from '@phosphor-icons/vue';

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
            <div class="stat-card stat-card-hover">
                <div class="stat-icon stat-icon-primary">
                    <PhCamera :size="26" weight="duotone" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.projects }}</div>
                    <div class="stat-label">Projets</div>
                </div>
            </div>
            <div class="stat-card stat-card-hover">
                <div class="stat-icon stat-icon-accent">
                    <PhImages :size="26" weight="duotone" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.photos }}</div>
                    <div class="stat-label">Photos</div>
                </div>
            </div>
            <div class="stat-card stat-card-hover">
                <div class="stat-icon stat-icon-success">
                    <PhTag :size="26" weight="duotone" />
                </div>
                <div>
                    <div class="stat-value">{{ stats.categories }}</div>
                    <div class="stat-label">Catégories</div>
                </div>
            </div>
            <div class="stat-card stat-card-hover">
                <div class="stat-icon stat-icon-warning">
                    <PhEnvelopeSimple :size="26" weight="duotone" />
                </div>
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
                <Link href="/admin/projects/create" class="btn btn-primary">
                    <PhPlus :size="16" weight="bold" />
                    <span>Nouveau projet</span>
                </Link>
                <Link href="/admin/categories" class="btn btn-ghost">
                    <PhTag :size="16" weight="duotone" />
                    <span>Gérer les catégories</span>
                </Link>
                <Link href="/admin/contacts" class="btn btn-ghost">
                    <PhChatsCircle :size="16" weight="duotone" />
                    <span>Voir les messages</span>
                </Link>
                <Link href="/admin/settings" class="btn btn-ghost">
                    <PhGear :size="16" weight="duotone" />
                    <span>Paramètres</span>
                </Link>
            </div>
        </div>

        <!-- Recent Messages -->
        <div v-if="recentContacts.length > 0" class="card" style="margin-top: var(--space-xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg);">
                <h3 style="margin: 0;">Derniers messages</h3>
                <Link href="/admin/contacts" class="btn btn-ghost btn-sm" style="display: inline-flex; align-items: center; gap: 4px;">
                    <span>Tout voir</span>
                    <PhArrowRight :size="14" weight="bold" />
                </Link>
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

.stat-card-hover {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease, border-color 0.3s ease;
    cursor: default;
    position: relative;
    overflow: hidden;
}

.stat-card-hover::after {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: var(--radius-lg);
    background: radial-gradient(circle at 80% 20%, rgba(157, 78, 221, 0.15), transparent 70%);
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
}

.stat-card-hover:hover {
    transform: translateY(-4px);
    border-color: rgba(157, 78, 221, 0.35);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4), 0 0 25px rgba(41, 62, 204, 0.15);
}

.stat-card-hover:hover::after {
    opacity: 1;
}
</style>
