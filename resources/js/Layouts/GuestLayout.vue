<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="auth-page">
        <div class="auth-card">
            <Link href="/">
                <ApplicationLogo />
            </Link>

            <slot />
        </div>
    </div>
</template>
