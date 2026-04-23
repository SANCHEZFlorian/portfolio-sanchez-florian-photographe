<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
    maxWidth: { type: String, default: '500px' },
});

const emit = defineEmits(['close']);

watch(() => props.show, (val) => {
    document.body.style.overflow = val ? 'hidden' : '';
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Teleport to="body">
        <Transition name="modal">
            <div v-if="show" class="modal-backdrop" @click.self="close">
                <div class="modal-container" :style="{ maxWidth: maxWidth }">
                    <div class="modal-header">
                        <h3 class="modal-title">{{ title }}</h3>
                        <button class="modal-close" @click="close">&times;</button>
                    </div>
                    <div class="modal-body">
                        <slot />
                    </div>
                    <div v-if="$slots.footer" class="modal-footer">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(8, 9, 26, 0.85);
    backdrop-filter: blur(10px);
    z-index: 9998;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-xl);
}

.modal-container {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-xl);
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-xl);
    border-bottom: 1px solid var(--border-color);
}

.modal-title {
    margin: 0;
    font-size: 1.2rem;
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 1.8rem;
    cursor: pointer;
    padding: 0;
    line-height: 1;
    transition: color var(--transition-fast);
}

.modal-close:hover {
    color: var(--text-color);
}

.modal-body {
    padding: var(--space-xl);
}

.modal-footer {
    padding: var(--space-lg) var(--space-xl);
    border-top: 1px solid var(--border-color);
    display: flex;
    justify-content: flex-end;
    gap: var(--space-sm);
}

/* Transition */
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.3s ease;
}

.modal-enter-active .modal-container,
.modal-leave-active .modal-container {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal-container {
    transform: scale(0.95) translateY(10px);
    opacity: 0;
}

.modal-leave-to .modal-container {
    transform: scale(0.95) translateY(10px);
    opacity: 0;
}
</style>
