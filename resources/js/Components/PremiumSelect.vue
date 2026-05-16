<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: [String, Number],
    options: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: 'Sélectionner...'
    },
    labelKey: {
        type: String,
        default: 'name'
    },
    valueKey: {
        type: String,
        default: 'id'
    }
});

const emit = defineEmits(['update:modelValue']);

const isOpen = ref(false);
const container = ref(null);

const toggle = () => isOpen.value = !isOpen.value;
const close = () => isOpen.value = false;

const selectOption = (option) => {
    emit('update:modelValue', option[props.valueKey]);
    close();
};

const selectedLabel = () => {
    const selected = props.options.find(opt => opt[props.valueKey] === props.modelValue);
    return selected ? selected[props.labelKey] : props.placeholder;
};

const handleClickOutside = (event) => {
    if (container.value && !container.value.contains(event.target)) {
        close();
    }
};

onMounted(() => document.addEventListener('click', handleClickOutside));
onUnmounted(() => document.removeEventListener('click', handleClickOutside));
</script>

<template>
    <div class="premium-select" ref="container">
        <div 
            class="select-trigger" 
            :class="{ 'is-open': isOpen }" 
            @click="toggle"
        >
            <span :class="{ 'is-placeholder': !modelValue }">{{ selectedLabel() }}</span>
            <span class="chevron">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </span>
        </div>

        <Transition name="dropdown">
            <div v-if="isOpen" class="select-dropdown">
                <div class="dropdown-inner">
                    <div 
                        v-for="option in options" 
                        :key="option[valueKey]"
                        class="select-option"
                        :class="{ 'is-active': option[valueKey] === modelValue }"
                        @click="selectOption(option)"
                    >
                        {{ option[labelKey] }}
                        <span v-if="option[valueKey] === modelValue" class="check">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        </span>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<style scoped>
.premium-select {
    position: relative;
    width: 100%;
    user-select: none;
}

.select-trigger {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1.25rem;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    color: var(--text-color);
    font-size: 0.95rem;
    cursor: pointer;
    transition: all var(--transition-base);
}

.select-trigger:hover {
    border-color: var(--primary-light);
    background: var(--surface-hover);
}

.select-trigger.is-open {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px var(--primary-glow);
}

.is-placeholder {
    color: var(--text-muted);
}

.chevron {
    transition: transform var(--transition-base);
    color: var(--text-muted);
}

.is-open .chevron {
    transform: rotate(180deg);
    color: var(--primary-light);
}

.select-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    right: 0;
    z-index: 50;
    padding: 6px;
    background: rgba(15, 18, 37, 0.95);
    backdrop-filter: blur(16px);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    transform-origin: top center;
}

.dropdown-inner {
    max-height: 250px;
    overflow-y: auto;
}

.select-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.7rem 1rem;
    border-radius: var(--radius-md);
    color: var(--text-secondary);
    font-size: 0.9rem;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.select-option:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-color);
    padding-left: 1.2rem;
}

.select-option.is-active {
    background: var(--primary-glow);
    color: var(--primary-light);
    font-weight: 500;
}

.check {
    color: var(--primary-light);
}

/* Animations */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.98);
}

/* Custom scrollbar for dropdown */
.dropdown-inner::-webkit-scrollbar {
    width: 4px;
}
.dropdown-inner::-webkit-scrollbar-thumb {
    background: var(--border-color);
    border-radius: 10px;
}
</style>
