<script setup>
import { ref, watch } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    categories: { type: Array, required: true },
    currentCategory: { type: String, default: 'all' },
});

const emit = defineEmits(['filter']);
const pillsRef = ref(null);

const select = (slug) => {
    emit('filter', slug);
};
</script>

<template>
    <div class="category-filter" ref="pillsRef">
        <button
            class="filter-pill"
            :class="{ active: currentCategory === 'all' }"
            @click="select('all')"
        >
            Tout
        </button>
        <button
            v-for="cat in categories"
            :key="cat.id"
            class="filter-pill"
            :class="{ active: currentCategory === cat.slug }"
            @click="select(cat.slug)"
        >
            {{ cat.name }}
            <span v-if="cat.projects_count !== undefined" class="filter-count">{{ cat.projects_count }}</span>
        </button>
    </div>
</template>

<style scoped>
.category-filter {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-sm);
    margin-bottom: var(--space-2xl);
}

.filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1.2rem;
    background: transparent;
    color: var(--text-muted);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-full);
    font-family: var(--font-body);
    font-size: 0.85rem;
    cursor: pointer;
    transition: all var(--transition-base);
}

.filter-pill:hover {
    color: var(--text-color);
    border-color: var(--primary);
    background: var(--primary-glow);
}

.filter-pill.active {
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    color: white;
    border-color: var(--primary);
    box-shadow: 0 4px 15px var(--primary-glow);
}

.filter-count {
    font-size: 0.7rem;
    background: rgba(255, 255, 255, 0.15);
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-full);
}
</style>
