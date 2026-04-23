<script setup>
import { ref } from 'vue';
import AdminModal from '@/Components/AdminModal.vue';

const props = defineProps({
    show: Boolean,
    title: { type: String, default: 'Confirmation' },
    message: { type: String, default: 'Êtes-vous sûr de vouloir effectuer cette action ?' },
    confirmText: { type: String, default: 'Confirmer' },
    cancelText: { type: String, default: 'Annuler' },
    type: { type: String, default: 'danger' }, // danger, primary
});

const emit = defineEmits(['close', 'confirm']);

const handleConfirm = () => {
    emit('confirm');
    emit('close');
};
</script>

<template>
    <AdminModal :show="show" :title="title" maxWidth="400px" @close="emit('close')">
        <div class="confirm-content">
            <div v-if="type === 'danger'" class="confirm-icon danger">⚠️</div>
            <div v-else class="confirm-icon primary">❓</div>
            <p class="confirm-message">{{ message }}</p>
        </div>

        <template #footer>
            <button class="btn btn-ghost" @click="emit('close')">{{ cancelText }}</button>
            <button 
                class="btn" 
                :class="type === 'danger' ? 'btn-danger' : 'btn-primary'"
                @click="handleConfirm"
            >
                {{ confirmText }}
            </button>
        </template>
    </AdminModal>
</template>

<style scoped>
.confirm-content {
    text-align: center;
    padding: var(--space-md) 0;
}

.confirm-icon {
    font-size: 3rem;
    margin-bottom: var(--space-md);
}

.confirm-message {
    font-size: 1.1rem;
    line-height: 1.5;
    color: var(--text-color);
}
</style>
