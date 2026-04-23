<script setup>
import { ref, onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    delay: { type: Number, default: 0 },
    y: { type: Number, default: 30 },
    x: { type: Number, default: 0 },
    duration: { type: Number, default: 0.8 },
    threshold: { type: Number, default: 0.15 },
});

const el = ref(null);
const isVisible = ref(false);

onMounted(() => {
    const observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting && !isVisible.value) {
                isVisible.value = true;
                gsap.fromTo(
                    el.value,
                    { y: props.y, x: props.x, opacity: 0 },
                    {
                        y: 0,
                        x: 0,
                        opacity: 1,
                        duration: props.duration,
                        delay: props.delay,
                        ease: 'power3.out',
                    }
                );
                observer.disconnect();
            }
        },
        { threshold: props.threshold }
    );

    if (el.value) {
        gsap.set(el.value, { opacity: 0 });
        observer.observe(el.value);
    }
});
</script>

<template>
    <div ref="el">
        <slot />
    </div>
</template>
