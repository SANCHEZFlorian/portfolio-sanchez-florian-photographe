<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import ToastList from '@/Components/ToastList.vue';
import PixiBackground from '@/Components/PixiBackground.vue';
import {
    PhGauge,
    PhTag,
    PhCamera,
    PhImages,
    PhEnvelopeSimple,
    PhGear,
    PhGlobe,
    PhUser,
    PhSignOut,
} from '@phosphor-icons/vue';

const page = usePage();

const navItems = [
    { label: 'Tableau de bord', icon: PhGauge, href: '/dashboard', match: '/dashboard' },
    { label: 'Catégories', icon: PhTag, href: '/admin/categories', match: '/admin/categories' },
    { label: 'Projets', icon: PhCamera, href: '/admin/projects', match: '/admin/projects' },
    { label: 'Photos (Vrac)', icon: PhImages, href: '/admin/photos', match: '/admin/photos' },
    { label: 'Messages', icon: PhEnvelopeSimple, href: '/admin/contacts', match: '/admin/contacts' },
    { label: 'Paramètres', icon: PhGear, href: '/admin/settings', match: '/admin/settings' },
];

const isActive = (match) => {
    return page.url.startsWith(match);
};
</script>

<template>
    <div class="admin-layout">
        <!-- Fond discret interactif PixiJS pour l'admin -->
        <PixiBackground mode="admin" :opacity="0.4" />
        <ToastList />
        <aside class="admin-sidebar">
            <div class="admin-sidebar-brand">
                <div class="brand-mark">FS</div>
                <div>
                    <div class="brand-text">Back-Office</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">Administration</div>
                </div>
            </div>

            <nav>
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="admin-nav-item"
                    :class="{ active: isActive(item.match) }"
                >
                    <component :is="item.icon" :size="20" weight="duotone" class="nav-icon" />
                    <span>{{ item.label }}</span>
                    <span v-if="item.label === 'Messages' && $page.props.unread_messages_count > 0" class="nav-badge">
                        {{ $page.props.unread_messages_count }}
                    </span>
                </Link>
            </nav>

            <div class="admin-sidebar-footer">
                <Link href="/" class="admin-nav-item" style="color: var(--primary-light);">
                    <PhGlobe :size="20" weight="duotone" class="nav-icon" />
                    <span>Voir le site</span>
                </Link>
                <Link :href="route('profile.edit')" class="admin-nav-item">
                    <PhUser :size="20" weight="duotone" class="nav-icon" />
                    <span>Profil</span>
                </Link>
                <Link :href="route('logout')" method="post" as="button" class="admin-nav-item" style="width: 100%; border: none; background: none; cursor: pointer; text-align: left; font-family: var(--font-body);">
                    <PhSignOut :size="20" weight="duotone" class="nav-icon" />
                    <span>Déconnexion</span>
                </Link>
            </div>
        </aside>

        <main class="admin-main">
            <header v-if="$slots.header" class="admin-header">
                <slot name="header" />
            </header>

            <slot />
        </main>
    </div>
</template>
