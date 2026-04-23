<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminModal from '@/Components/AdminModal.vue';

const props = defineProps({
    contacts: Array,
    unreadCount: Number,
});

const viewingContact = ref(null);

const toggleRead = (contact) => {
    router.patch(`/admin/contacts/${contact.id}/toggle-read`, {}, {
        preserveScroll: true,
    });
};

const deleteContact = (contact) => {
    if (confirm(`Supprimer le message de ${contact.name} ?`)) {
        router.delete(`/admin/contacts/${contact.id}`, {
            preserveScroll: true,
        });
    }
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Messages" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 style="margin: 0;">Messages</h2>
                <span v-if="unreadCount > 0" style="font-size: 0.85rem; color: var(--text-muted);">
                    {{ unreadCount }} non lu(s)
                </span>
            </div>
        </template>

        <div class="card" v-if="contacts.length > 0">
            <table>
                <thead>
                    <tr>
                        <th>État</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Sujet</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="contact in contacts" :key="contact.id" :style="{ opacity: contact.is_read ? 0.7 : 1 }">
                        <td>
                            <span
                                :class="contact.is_read ? 'badge badge-success' : 'badge badge-warning'"
                                style="cursor: pointer;"
                                @click="toggleRead(contact)"
                            >
                                {{ contact.is_read ? 'Lu' : 'Non lu' }}
                            </span>
                        </td>
                        <td :style="{ fontWeight: contact.is_read ? 400 : 600 }">{{ contact.name }}</td>
                        <td><a :href="`mailto:${contact.email}`">{{ contact.email }}</a></td>
                        <td>{{ contact.subject || '—' }}</td>
                        <td style="font-size: 0.85rem; color: var(--text-muted);">{{ formatDate(contact.created_at) }}</td>
                        <td>
                            <div style="display: flex; gap: var(--space-sm);">
                                <button class="btn btn-ghost btn-sm" @click="viewingContact = contact">Voir</button>
                                <button class="btn btn-danger btn-sm" @click="deleteContact(contact)">🗑️</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-else class="empty-state">
            <div class="empty-state-icon">✉️</div>
            <div class="empty-state-title">Aucun message</div>
            <div class="empty-state-text">Les messages reçus via le formulaire de contact apparaîtront ici.</div>
        </div>

        <!-- View Message Modal -->
        <AdminModal :show="!!viewingContact" @close="viewingContact = null" title="Message" maxWidth="600px">
            <div v-if="viewingContact">
                <div class="msg-field">
                    <div class="msg-label">De</div>
                    <div>{{ viewingContact.name }} — <a :href="`mailto:${viewingContact.email}`">{{ viewingContact.email }}</a></div>
                </div>
                <div v-if="viewingContact.subject" class="msg-field">
                    <div class="msg-label">Sujet</div>
                    <div>{{ viewingContact.subject }}</div>
                </div>
                <div class="msg-field">
                    <div class="msg-label">Date</div>
                    <div>{{ formatDate(viewingContact.created_at) }}</div>
                </div>
                <div class="msg-field">
                    <div class="msg-label">Message</div>
                    <div class="msg-body">{{ viewingContact.message }}</div>
                </div>

                <div style="display: flex; gap: var(--space-sm); justify-content: flex-end; margin-top: var(--space-xl);">
                    <button class="btn btn-ghost" @click="toggleRead(viewingContact); viewingContact = null;">
                        {{ viewingContact.is_read ? 'Marquer non lu' : 'Marquer comme lu' }}
                    </button>
                    <a :href="`mailto:${viewingContact.email}`" class="btn btn-primary">Répondre</a>
                </div>
            </div>
        </AdminModal>
    </AuthenticatedLayout>
</template>

<style scoped>
.msg-field {
    margin-bottom: var(--space-lg);
}

.msg-label {
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: var(--text-muted);
    margin-bottom: var(--space-xs);
}

.msg-body {
    white-space: pre-wrap;
    line-height: 1.7;
    color: var(--text-secondary);
    background: var(--bg-elevated);
    padding: var(--space-lg);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-subtle);
}
</style>
