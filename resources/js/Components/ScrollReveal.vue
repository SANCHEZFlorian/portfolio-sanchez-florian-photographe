<script setup>
import { ref, onMounted } from 'vue';
import gsap from 'gsap';

const props = defineProps({
    delay: { type: Number, default: 0 },
    y: { type: Number, default: 35 },
    x: { type: Number, default: 0 },
    scale: { type: Number, default: 0.97 },
    blur: { type: Boolean, default: true },
    duration: { type: Number, default: 0.9 },
    threshold: { type: Number, default: 0.12 },
});

const el = ref(null);
const isVisible = ref(false);

onMounted(() => {
    const observer = new IntersectionObserver(
        ([entry]) => {
            if (entry.isIntersecting && !isVisible.value) {
                isVisible.value = true;
                const fromVars = {
                    y: props.y,
                    x: props.x,
                    scale: props.scale,
                    opacity: 0,
                };

                if (props.blur) {
                    fromVars.filter = 'blur(6px)';
                }

                const toVars = {
                    y: 0,
                    x: 0,
                    scale: 1,
                    opacity: 1,
                    duration: props.duration,
                    delay: props.delay,
                    ease: 'power3.out',
                    clearProps: 'transform,opacity,filter',
                };

                if (props.blur) {
                    toVars.filter = 'blur(0px)';
                }

                gsap.fromTo(el.value, fromVars, toVars);
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
    <div ref="el" class="scroll-reveal-wrapper">
        <slot />
    </div>
</template>

<style scoped>
.scroll-reveal-wrapper {
    will-change: transform, opacity;
}
</style>
