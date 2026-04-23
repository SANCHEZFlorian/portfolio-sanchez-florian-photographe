<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    message: { type: String, required: true },
    type: { type: String, default: 'success' }, // success, error, info
    duration: { type: Number, default: 4000 },
});

const emit = defineEmits(['dismiss']);
const visible = ref(true);

onMounted(() => {
    setTimeout(() => {
        visible.value = false;
        setTimeout(() => emit('dismiss'), 400);
    }, props.duration);
});
</script>

<template>
    <Transition name="toast">
        <div v-if="visible" class="toast" :class="`toast-${type}`" @click="visible = false">
            <span v-if="type === 'success'">✓</span>
            <span v-else-if="type === 'error'">✕</span>
            <span v-else>ℹ</span>
            {{ message }}
        </div>
    </Transition>
</template>

<style scoped>
.toast-enter-active {
    animation: toastIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.toast-leave-active {
    animation: toastOut 0.3s ease forwards;
}

@keyframes toastOut {
    to {
        transform: translateX(120%);
        opacity: 0;
    }
}
</style>
