<script setup>
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Florian Sanchez | Photographe Professionnel</title>
        <meta name="description" content="Portfolio de Florian Sanchez, photographe professionnel." />
    </Head>

    <div class="min-h-screen flex items-center justify-center bg-[#060911] text-white p-6">
        <div class="text-center max-w-md">
            <h1 class="text-3xl font-serif font-light mb-4">Florian Sanchez</h1>
            <p class="text-gray-400 mb-6">Photographe Professionnel</p>
            <Link href="/" class="inline-block px-6 py-3 rounded-full bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition">
                Accéder au portfolio
            </Link>
        </div>
    </div>
</template>
