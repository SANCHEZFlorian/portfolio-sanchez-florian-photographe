<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Toast from '@/Components/Toast.vue';

const page = usePage();
const toasts = ref([]);

const addToast = (message, type = 'success') => {
    if (!message) return;
    const id = Date.now() + Math.random();
    toasts.value.push({ id, message, type });
};

// Listen for flash messages from Inertia
watch(() => page.props.flash, (flash) => {
    if (flash.success) addToast(flash.success, 'success');
    if (flash.error) addToast(flash.error, 'error');
    if (flash.info) addToast(flash.info, 'info');
}, { deep: true });

// Initial check on mount
onMounted(() => {
    if (page.props.flash.success) addToast(page.props.flash.success, 'success');
    if (page.props.flash.error) addToast(page.props.flash.error, 'error');
    if (page.props.flash.info) addToast(page.props.flash.info, 'info');
});

const removeToast = (id) => {
    toasts.value = toasts.value.filter(t => t.id !== id);
};
</script>

<template>
    <div class="toast-container">
        <Toast
            v-for="toast in toasts"
            :key="toast.id"
            :message="toast.message"
            :type="toast.type"
            @dismiss="removeToast(toast.id)"
        />
    </div>
</template>

<style scoped>
.toast-container {
    position: fixed;
    top: var(--space-xl);
    right: var(--space-xl);
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    z-index: 10000;
    pointer-events: none;
}

.toast-container > * {
    pointer-events: auto;
}
</style>
