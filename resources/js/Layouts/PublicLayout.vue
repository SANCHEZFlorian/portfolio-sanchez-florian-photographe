<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ToastList from '@/Components/ToastList.vue';

const page = usePage();
const isScrolled = ref(false);
const mobileOpen = ref(false);

const navItems = [
    { label: 'Accueil', href: '/', routeName: 'home' },
    { label: 'Projets', href: '/projets', routeName: 'projects' },
    { label: 'Galerie', href: '/galerie', routeName: 'gallery' },
    { label: 'À propos', href: '/a-propos', routeName: 'about' },
    { label: 'Contact', href: '/contact', routeName: 'contact' },
];

const isActive = (routeName) => {
    const url = page.url;
    if (routeName === 'home') return url === '/';
    if (routeName === 'projects') return url.startsWith('/projets');
    if (routeName === 'gallery') return url.startsWith('/galerie');
    if (routeName === 'about') return url === '/a-propos';
    if (routeName === 'contact') return url === '/contact';
    return false;
};

const handleScroll = () => {
    isScrolled.value = window.scrollY > 50;
};

const toggleMobile = () => {
    mobileOpen.value = !mobileOpen.value;
    document.body.style.overflow = mobileOpen.value ? 'hidden' : '';
};

const closeMobile = () => {
    mobileOpen.value = false;
    document.body.style.overflow = '';
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <div class="public-page">
        <ToastList />
        <!-- Navigation -->
        <nav class="public-nav" :class="{ scrolled: isScrolled }">
            <Link href="/" class="nav-logo" @click="closeMobile">
                <span class="nav-logo-mark">FS</span>
                <span class="nav-logo-text">Florian Sanchez</span>
            </Link>

            <ul class="nav-links" :class="{ open: mobileOpen }">
                <li v-for="item in navItems" :key="item.routeName">
                    <Link
                        :href="item.href"
                        :class="{ active: isActive(item.routeName) }"
                        @click="closeMobile"
                    >
                        {{ item.label }}
                    </Link>
                </li>
                <li v-if="page.props.auth?.user">
                    <Link href="/dashboard" class="admin-dash-link" @click="closeMobile">
                        Dashboard
                    </Link>
                </li>
            </ul>

            <button class="nav-toggle" @click="toggleMobile" :class="{ active: mobileOpen }">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </nav>

        <!-- Main Content -->
        <main>
            <slot />
        </main>

        <!-- Footer -->
        <footer class="site-footer">
            <div class="footer-content">
                <div class="footer-copy">
                    &copy; {{ new Date().getFullYear() }} Florian Sanchez — Photographe
                </div>
                <div class="footer-links">
                    <Link href="/projets">Projets</Link>
                    <Link href="/galerie">Galerie</Link>
                    <Link href="/a-propos">À propos</Link>
                    <Link href="/contact">Contact</Link>
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.public-page {
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.public-page main {
    flex: 1;
    padding-top: var(--nav-height);
}

.admin-dash-link {
    font-size: 0.8rem !important;
    padding: 0.35rem 0.8rem !important;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-full);
    color: var(--text-muted) !important;
    transition: all var(--transition-base);
}

.admin-dash-link:hover {
    border-color: var(--primary);
    color: var(--primary-light) !important;
    background: var(--primary-glow);
}

/* Burger Animation */
.nav-toggle.active span:nth-child(1) {
    transform: rotate(45deg) translateY(5px) translateX(5px);
}
.nav-toggle.active span:nth-child(2) {
    opacity: 0;
}
.nav-toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translateY(-5px) translateX(5px);
}
</style>
