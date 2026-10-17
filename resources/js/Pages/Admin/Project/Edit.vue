<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminModal from '@/Components/AdminModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import PremiumSelect from '@/Components/PremiumSelect.vue';

const props = defineProps({
    project: Object,
    categories: Array,
});

// Mode d'affichage de la gestion des photos : 'mosaic' (WYSIWYG réel) ou 'compact' (cartes simples)
const viewMode = ref('mosaic');

// Formats photographiques disponibles (tailles divisées par deux)
const PHOTO_FORMATS = [
    {
        id: 'landscape-sm',
        cols: 3,
        rows: 2,
        label: 'Petit Paysage',
        shortLabel: 'P. Paysage',
        icon: '🖼️',
        desc: 'Ratio 3:2 — Quart de largeur (4 par rangée)',
    },
    {
        id: 'landscape-lg',
        cols: 6,
        rows: 4,
        label: 'Grand Paysage',
        shortLabel: 'G. Paysage',
        icon: '🖼️',
        desc: 'Ratio 3:2 — Moitié de largeur (2 par rangée)',
    },
    {
        id: 'portrait-sm',
        cols: 2,
        rows: 3,
        label: 'Petit Portrait',
        shortLabel: 'P. Portrait',
        icon: '📱',
        desc: 'Ratio 2:3 — Un sixième de largeur (6 par rangée)',
    },
    {
        id: 'portrait-lg',
        cols: 4,
        rows: 6,
        label: 'Grand Portrait',
        shortLabel: 'G. Portrait',
        icon: '📱',
        desc: 'Ratio 2:3 — Un tiers de largeur (3 par rangée)',
    },
];

const getCurrentFormatId = (photo) => {
    const c = photo.span_cols || 3;
    const r = photo.span_rows || 2;
    if (c === 6 && r === 4) return 'landscape-lg';
    if (c === 3 && r === 2) return 'landscape-sm';
    if (c === 4 && r === 6) return 'portrait-lg';
    if (c === 2 && r === 3) return 'portrait-sm';
    // Rétrocompatibilité avec les tailles précédentes
    if (c === 12 && r === 8) return 'landscape-lg';
    if (c === 6 && r === 9) return 'portrait-lg';
    return c >= r ? 'landscape-sm' : 'portrait-sm';
};

// Project meta form
const form = useForm({
    title: props.project.title,
    category_id: props.project.category_id,
    description: props.project.description || '',
    instagram_url: props.project.instagram_url || '',
    date: props.project.date ? props.project.date.split('T')[0] : '',
    location: props.project.location || '',
    is_featured: props.project.is_featured || false,
    status: props.project.status || 'published',
});

const submitMeta = () => {
    form.put(`/admin/projects/${props.project.id}`, { preserveScroll: true });
};

// Photo upload
const uploading = ref(false);
const isDragOver = ref(false);

const handleDrop = (e) => {
    isDragOver.value = false;
    const files = Array.from(e.dataTransfer.files);
    if (files.length > 0) {
        processUpload(files);
    }
};

const uploadPhotos = (e) => {
    const files = Array.from(e.target.files);
    if (files.length > 0) {
        processUpload(files);
        e.target.value = '';
    }
};

const processUpload = (files) => {
    const MAX_SIZE = 100 * 1024 * 1024; // 100MB
    let totalSize = 0;

    for (const file of files) {
        if (file.size > MAX_SIZE) {
            alert(`Le fichier ${file.name} est trop lourd (max 100Mo).`);
            return;
        }
        totalSize += file.size;
    }

    if (totalSize > MAX_SIZE) {
        alert("Le total des fichiers dépasse 100Mo. Veuillez uploader en plusieurs fois.");
        return;
    }

    const formData = new FormData();
    files.forEach(f => formData.append('photos[]', f));

    uploading.value = true;
    router.post(`/admin/projects/${props.project.id}/photos`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            uploading.value = false;
        },
    });
};

// Photo editing modal
const editingPhoto = ref(null);
const photoForm = useForm({
    caption: '',
    instagram_tag: '',
    span_cols: 3,
    span_rows: 2,
    display_order: 0,
    is_highlighted: false,
    is_cover: false,
});

const openPhotoEdit = (photo) => {
    editingPhoto.value = photo;
    photoForm.caption = photo.caption || '';
    photoForm.instagram_tag = photo.instagram_tag || '';
    photoForm.span_cols = photo.span_cols || 3;
    photoForm.span_rows = photo.span_rows || 2;
    photoForm.display_order = photo.display_order;
    photoForm.is_highlighted = photo.is_highlighted || false;
    photoForm.is_cover = photo.is_cover || false;
};

const savePhoto = () => {
    photoForm.put(`/admin/projects/${props.project.id}/photos/${editingPhoto.value.id}`, {
        preserveScroll: true,
        onSuccess: () => { editingPhoto.value = null; },
    });
};

// Deletion confirmation
const confirmDeletion = ref(null);
const deletePhoto = (photoId) => {
    confirmDeletion.value = photoId;
};

const executeDelete = () => {
    const photoId = confirmDeletion.value;
    router.delete(`/admin/projects/${props.project.id}/photos/${photoId}`, {
        preserveScroll: true,
        onSuccess: () => { confirmDeletion.value = null; },
    });
};

// Quick format change
const quickSetFormat = (photo, format) => {
    photo.span_cols = format.cols;
    photo.span_rows = format.rows;

    router.put(`/admin/projects/${props.project.id}/photos/${photo.id}`, {
        caption: photo.caption,
        instagram_tag: photo.instagram_tag,
        span_cols: format.cols,
        span_rows: format.rows,
        display_order: photo.display_order,
        is_highlighted: photo.is_highlighted,
        is_cover: photo.is_cover,
    }, { preserveScroll: true });
};

// Quick toggle Cover
const toggleCover = (photo) => {
    const newCover = !photo.is_cover;
    if (newCover) {
        props.project.photos.forEach(p => p.is_cover = false);
    }
    photo.is_cover = newCover;

    router.put(`/admin/projects/${props.project.id}/photos/${photo.id}`, {
        caption: photo.caption,
        instagram_tag: photo.instagram_tag,
        span_cols: photo.span_cols,
        span_rows: photo.span_rows,
        display_order: photo.display_order,
        is_highlighted: photo.is_highlighted,
        is_cover: newCover,
    }, { preserveScroll: true });
};

// Quick toggle Highlight (coup de cœur)
const toggleHighlight = (photo) => {
    photo.is_highlighted = !photo.is_highlighted;

    router.put(`/admin/projects/${props.project.id}/photos/${photo.id}`, {
        caption: photo.caption,
        instagram_tag: photo.instagram_tag,
        span_cols: photo.span_cols,
        span_rows: photo.span_rows,
        display_order: photo.display_order,
        is_highlighted: photo.is_highlighted,
        is_cover: photo.is_cover,
    }, { preserveScroll: true });
};

// Drag & Drop reorder
const draggingIndex = ref(null);
const dragOverIndex = ref(null);

const onDragStart = (e, index) => {
    draggingIndex.value = index;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', index);
};

const onDragOver = (e, index) => {
    e.preventDefault();
    dragOverIndex.value = index;
    e.dataTransfer.dropEffect = 'move';
};

const onDragLeave = () => {
    dragOverIndex.value = null;
};

const onDrop = (e, targetIndex) => {
    e.preventDefault();
    if (draggingIndex.value === null || draggingIndex.value === targetIndex) {
        draggingIndex.value = null;
        dragOverIndex.value = null;
        return;
    }

    // Réordonnancement optimiste local immédiat
    const photos = [...props.project.photos];
    const [moved] = photos.splice(draggingIndex.value, 1);
    photos.splice(targetIndex, 0, moved);

    photos.forEach((p, idx) => p.display_order = idx);
    props.project.photos.splice(0, props.project.photos.length, ...photos);

    const order = photos.map((p, i) => ({ id: p.id, display_order: i }));

    router.post(`/admin/projects/${props.project.id}/photos/reorder`, { order }, {
        preserveScroll: true,
    });

    draggingIndex.value = null;
    dragOverIndex.value = null;
};

// Grille unitaire calculée pour la prévisualisation Admin
const adminGridRef = ref(null);
let resizeObserver = null;

const updateAdminGridUnit = () => {
    if (!adminGridRef.value) return;
    const width = adminGridRef.value.clientWidth;
    if (!width) return;
    const gap = 16;
    const cols = 12;
    const colWidth = (width - (cols - 1) * gap) / cols;
    adminGridRef.value.style.setProperty('--grid-unit', `${colWidth}px`);
    adminGridRef.value.style.setProperty('--grid-cols', cols);
};

onMounted(() => {
    updateAdminGridUnit();
    if (window.ResizeObserver && adminGridRef.value) {
        resizeObserver = new ResizeObserver(() => updateAdminGridUnit());
        resizeObserver.observe(adminGridRef.value);
    }
});

onUnmounted(() => {
    if (resizeObserver) resizeObserver.disconnect();
});
</script>

<template>
    <Head :title="`Éditer: ${project.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex-between">
                <div>
                    <h2>{{ project.title }}</h2>
                    <span style="font-size: 0.85rem; color: var(--text-muted);">
                        Gestion des clichés et composition de la mosaïque
                    </span>
                </div>
                <div style="display: flex; gap: var(--space-sm);">
                    <a :href="`/projets/${project.slug}`" target="_blank" class="btn btn-secondary btn-sm">
                        👁️ Voir sur le site
                    </a>
                    <Link href="/admin/projects" class="btn btn-ghost btn-sm">← Projets</Link>
                </div>
            </div>
        </template>

        <div style="display: grid; grid-template-columns: 340px 1fr; gap: var(--space-xl);">
            <!-- Left: Project Meta -->
            <div>
                <div class="card">
                    <h3 style="color: var(--primary-light); margin-bottom: var(--space-lg);">Informations</h3>
                    <form @submit.prevent="submitMeta">
                        <div class="form-group">
                            <label class="form-label">Titre</label>
                            <input type="text" v-model="form.title" required />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Catégorie</label>
                            <PremiumSelect 
                                v-model="form.category_id"
                                :options="categories"
                                placeholder="Sélectionner une catégorie"
                            />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Date</label>
                            <input type="date" v-model="form.date" />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Lieu</label>
                            <input type="text" v-model="form.location" placeholder="Paris..." />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Instagram</label>
                            <input type="url" v-model="form.instagram_url" placeholder="https://instagram.com/..." />
                        </div>

                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea v-model="form.description" rows="3"></textarea>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: var(--space-sm); margin-bottom: var(--space-lg);">
                            <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer; font-size: 0.9rem; color: var(--text-secondary);">
                                <input type="checkbox" v-model="form.is_featured" style="width: auto; accent-color: var(--primary);" />
                                En avant sur l'accueil
                            </label>
                            <div style="flex: 1;">
                                <label class="form-label">Statut</label>
                                <PremiumSelect 
                                    v-model="form.status"
                                    :options="[
                                        { id: 'published', name: 'Publié' },
                                        { id: 'draft', name: 'Brouillon' }
                                    ]"
                                />
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width: 100%;" :disabled="form.processing">
                            {{ form.processing ? 'Sauvegarde...' : 'Mettre à jour' }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Right: Photos Manager -->
            <div>
                <!-- Upload Zone -->
                <div class="card" style="margin-bottom: var(--space-xl);">
                    <div class="flex-between" style="margin-bottom: var(--space-md);">
                        <h3 style="margin: 0;">Ajouter des photos</h3>
                        <span style="font-size: 0.85rem; color: var(--text-muted);">
                            Format détecté automatiquement à l'upload (Paysage / Portrait)
                        </span>
                    </div>
                    <label 
                        class="upload-zone" 
                        :class="{ active: uploading || isDragOver }"
                        @dragover.prevent="isDragOver = true"
                        @dragleave.prevent="isDragOver = false"
                        @drop.prevent="handleDrop"
                    >
                        <input type="file" multiple accept="image/*" @change="uploadPhotos" style="display: none;" />
                        <div v-if="uploading" style="color: var(--accent);">
                            ⏳ Téléversement et optimisation WebP en cours...
                        </div>
                        <div v-else>
                            <div style="font-size: 2rem; margin-bottom: var(--space-sm);">📷</div>
                            <div style="color: var(--text-secondary); font-weight: 500;">
                                {{ isDragOver ? 'Relâchez pour uploader !' : 'Cliquez ou glissez vos photos ici' }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: var(--space-xs);">
                                Cadrage original 100% préservé — Fichiers optimisés automatiquement
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Photos Section Header & View Mode Switcher -->
                <div class="card" style="margin-bottom: var(--space-lg); padding: var(--space-md) var(--space-lg);">
                    <div class="flex-between" style="flex-wrap: wrap; gap: var(--space-md);">
                        <div>
                            <h3 style="margin: 0; font-size: 1.15rem;">
                                Photos du projet ({{ project.photos.length }})
                            </h3>
                            <span style="font-size: 0.8rem; color: var(--text-muted);">
                                💡 Glissez-déposez les photos pour recomposer la mosaïque en direct
                            </span>
                        </div>

                        <!-- Mode toggle -->
                        <div class="view-mode-tabs">
                            <button
                                type="button"
                                class="view-mode-btn"
                                :class="{ active: viewMode === 'mosaic' }"
                                @click="viewMode = 'mosaic'; $nextTick(updateAdminGridUnit)"
                            >
                                🖼️ Mosaïque en direct (WYSIWYG)
                            </button>
                            <button
                                type="button"
                                class="view-mode-btn"
                                :class="{ active: viewMode === 'compact' }"
                                @click="viewMode = 'compact'"
                            >
                                📋 Grille compacte
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 1. VUE MOSAÏQUE RÉELLE (WYSIWYG INTERACTIVE) -->
                <div v-if="project.photos.length > 0 && viewMode === 'mosaic'">
                    <div class="admin-mosaic-grid" ref="adminGridRef">
                        <div
                            v-for="(photo, index) in project.photos"
                            :key="photo.id"
                            class="admin-mosaic-item"
                            :class="[
                                `span-c-${photo.span_cols || 3}`,
                                `span-r-${photo.span_rows || 2}`,
                                {
                                    'is-dragging': draggingIndex === index,
                                    'is-drag-over': dragOverIndex === index,
                                    'is-cover': photo.is_cover,
                                    'is-highlighted': photo.is_highlighted
                                }
                            ]"
                            draggable="true"
                            @dragstart="onDragStart($event, index)"
                            @dragover="onDragOver($event, index)"
                            @dragleave="onDragLeave"
                            @drop="onDrop($event, index)"
                        >
                            <!-- Image avec ratio exact sans rognage -->
                            <div class="admin-mosaic-img-wrap">
                                <img
                                    :src="`/storage/${photo.thumbnail_path || photo.image_path}`"
                                    :alt="photo.caption || 'Photo'"
                                    loading="lazy"
                                />
                            </div>

                            <!-- Top bar: Order, badges, quick actions -->
                            <div class="admin-mosaic-topbar">
                                <div class="admin-mosaic-badge-order" :title="`Ordre #${index + 1} · Format: ${photo.span_cols >= photo.span_rows ? 'Paysage' : 'Portrait'}`">
                                    <span class="drag-handle">⠿</span> #{{ index + 1 }}
                                    <span style="opacity: 0.7; font-size: 0.7rem;">{{ photo.span_cols >= photo.span_rows ? '🖼️' : '📱' }}</span>
                                </div>
                                <div class="admin-mosaic-top-actions">
                                    <button
                                        type="button"
                                        class="badge-toggle"
                                        :class="{ active: photo.is_cover }"
                                        @click.stop="toggleCover(photo)"
                                        title="Photo de couverture"
                                    >
                                        {{ photo.is_cover ? '★ Cover' : 'Cover' }}
                                    </button>
                                    <button
                                        type="button"
                                        class="badge-toggle"
                                        :class="{ active: photo.is_highlighted }"
                                        @click.stop="toggleHighlight(photo)"
                                        title="Coup de cœur accueil"
                                    >
                                        {{ photo.is_highlighted ? '★ Star' : '☆' }}
                                    </button>
                                </div>
                            </div>

                            <!-- Bottom bar: Format selector & edit/delete -->
                            <div class="admin-mosaic-bottombar">
                                <div class="format-buttons-group">
                                    <button
                                        v-for="fmt in PHOTO_FORMATS"
                                        :key="fmt.id"
                                        type="button"
                                        class="fmt-btn"
                                        :class="{ active: getCurrentFormatId(photo) === fmt.id }"
                                        @click.stop="quickSetFormat(photo, fmt)"
                                        :title="`${fmt.label} (${fmt.desc})`"
                                    >
                                        <span class="fmt-icon">{{ fmt.icon }}</span>
                                        <span class="fmt-text">{{ fmt.shortLabel }}</span>
                                    </button>
                                </div>

                                <div class="admin-card-edit-btns">
                                    <button
                                        type="button"
                                        class="btn-icon"
                                        @click.stop="openPhotoEdit(photo)"
                                        title="Modifier légende et tags"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        type="button"
                                        class="btn-icon btn-icon-danger"
                                        @click.stop="deletePhoto(photo.id)"
                                        title="Supprimer la photo"
                                    >
                                        🗑️
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. VUE GRILLE COMPACTE (CARTES SIMPLES) -->
                <div v-else-if="project.photos.length > 0 && viewMode === 'compact'" class="photos-admin-compact-grid">
                    <div
                        v-for="(photo, index) in project.photos"
                        :key="photo.id"
                        class="photo-compact-card"
                        draggable="true"
                        @dragstart="onDragStart($event, index)"
                        @dragover="onDragOver($event, index)"
                        @dragleave="onDragLeave"
                        @drop="onDrop($event, index)"
                        :class="{
                            'is-dragging': draggingIndex === index,
                            'is-drag-over': dragOverIndex === index,
                            'is-cover': photo.is_cover,
                            'is-highlighted': photo.is_highlighted
                        }"
                    >
                        <div class="photo-compact-img">
                            <img :src="`/storage/${photo.thumbnail_path || photo.image_path}`" :alt="photo.caption || 'Photo'" />
                            <div class="photo-compact-order">#{{ index + 1 }}</div>
                            <div class="photo-compact-badges">
                                <span v-if="photo.is_cover" class="badge badge-accent" style="font-size: 0.65rem;">Cover</span>
                                <span v-if="photo.is_highlighted" class="badge badge-primary" style="font-size: 0.65rem;">★</span>
                            </div>
                        </div>

                        <div class="photo-compact-actions">
                            <div class="format-buttons-group" style="margin-bottom: var(--space-xs);">
                                <button
                                    v-for="fmt in PHOTO_FORMATS"
                                    :key="fmt.id"
                                    type="button"
                                    class="fmt-btn"
                                    :class="{ active: getCurrentFormatId(photo) === fmt.id }"
                                    @click="quickSetFormat(photo, fmt)"
                                    :title="fmt.label"
                                >
                                    {{ fmt.shortLabel }}
                                </button>
                            </div>

                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; gap: 4px;">
                                    <button
                                        type="button"
                                        class="badge-toggle"
                                        :class="{ active: photo.is_cover }"
                                        @click="toggleCover(photo)"
                                        style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"
                                    >
                                        Cover
                                    </button>
                                    <button
                                        type="button"
                                        class="badge-toggle"
                                        :class="{ active: photo.is_highlighted }"
                                        @click="toggleHighlight(photo)"
                                        style="font-size: 0.7rem; padding: 0.15rem 0.4rem;"
                                    >
                                        ★
                                    </button>
                                </div>
                                <div style="display: flex; gap: var(--space-xs);">
                                    <button class="btn btn-ghost btn-sm" @click="openPhotoEdit(photo)">✏️</button>
                                    <button class="btn btn-danger btn-sm" @click="deletePhoto(photo.id)">🗑️</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <div class="empty-state-icon">🖼️</div>
                    <div class="empty-state-title">Aucune photo dans ce shooting</div>
                    <div class="empty-state-text">Glissez des photos dans la zone ci-dessus pour composer votre galerie.</div>
                </div>
            </div>
        </div>

        <!-- Photo Edit Modal -->
        <AdminModal :show="!!editingPhoto" @close="editingPhoto = null" title="Modifier la photo">
            <form v-if="editingPhoto" @submit.prevent="savePhoto">
                <div style="margin-bottom: var(--space-lg); text-align: center;">
                    <img :src="`/storage/${editingPhoto.thumbnail_path || editingPhoto.image_path}`" style="max-height: 220px; border-radius: var(--radius-md); object-fit: contain;" />
                </div>

                <div class="form-group">
                    <label class="form-label">Format d'affichage dans la mosaïque</label>
                    <div class="format-modal-selector">
                        <button
                            v-for="fmt in PHOTO_FORMATS"
                            :key="fmt.id"
                            type="button"
                            class="fmt-modal-card"
                            :class="{ active: photoForm.span_cols === fmt.cols && photoForm.span_rows === fmt.rows }"
                            @click="photoForm.span_cols = fmt.cols; photoForm.span_rows = fmt.rows"
                        >
                            <span class="fmt-modal-icon">{{ fmt.icon }}</span>
                            <span class="fmt-modal-title">{{ fmt.label }}</span>
                            <span class="fmt-modal-desc">{{ fmt.desc }}</span>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Légende</label>
                    <input type="text" v-model="photoForm.caption" placeholder="Description de la photo..." />
                </div>

                <div class="form-group">
                    <label class="form-label">Tag Instagram</label>
                    <input type="text" v-model="photoForm.instagram_tag" placeholder="@username" />
                </div>

                <div class="form-group">
                    <label class="form-label">Ordre d'affichage</label>
                    <input type="number" v-model="photoForm.display_order" />
                </div>

                <div style="display: flex; gap: var(--space-xl); margin-bottom: var(--space-xl);">
                    <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer; font-size: 0.9rem; color: var(--text-secondary);">
                        <input type="checkbox" v-model="photoForm.is_highlighted" style="width: auto; accent-color: var(--primary);" />
                        Coup de cœur (accueil)
                    </label>
                    <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer; font-size: 0.9rem; color: var(--text-secondary);">
                        <input type="checkbox" v-model="photoForm.is_cover" style="width: auto; accent-color: var(--accent);" />
                        Photo de couverture
                    </label>
                </div>

                <div style="display: flex; gap: var(--space-sm); justify-content: flex-end;">
                    <button type="button" class="btn btn-ghost" @click="editingPhoto = null">Annuler</button>
                    <button type="submit" class="btn btn-primary" :disabled="photoForm.processing">
                        {{ photoForm.processing ? 'Sauvegarde...' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </AdminModal>

        <!-- Deletion Confirmation -->
        <ConfirmModal
            :show="!!confirmDeletion"
            title="Supprimer la photo"
            message="Êtes-vous sûr de vouloir supprimer cette photo ? Cette action est irréversible."
            @close="confirmDeletion = null"
            @confirm="executeDelete"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
/* Tabs Switcher */
.view-mode-tabs {
    display: inline-flex;
    background: var(--bg-deep);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-full);
    padding: 3px;
    gap: 4px;
}

.view-mode-btn {
    border: none;
    background: transparent;
    color: var(--text-muted);
    font-size: 0.82rem;
    font-weight: 500;
    padding: 0.4rem 0.9rem;
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.view-mode-btn:hover {
    color: var(--text-color);
}

.view-mode-btn.active {
    background: var(--primary);
    color: white;
    box-shadow: 0 2px 8px var(--primary-glow);
}

/* --- Admin Mosaic Grid (Exact Replica of Public Grid) --- */
.admin-mosaic-grid {
    display: grid;
    grid-template-columns: repeat(var(--grid-cols, 12), 1fr);
    grid-auto-rows: var(--grid-unit, 70px);
    grid-auto-flow: dense;
    gap: 16px;
    width: 100%;
}

.admin-mosaic-item {
    position: relative;
    border-radius: var(--radius-md);
    overflow: hidden;
    background-color: var(--surface-color);
    box-shadow: var(--shadow-sm);
    border: 2px solid transparent;
    cursor: grab;
    transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    user-select: none;
}

.admin-mosaic-item:hover {
    border-color: rgba(99, 102, 241, 0.4);
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
}

.admin-mosaic-item.is-dragging {
    opacity: 0.4;
    cursor: grabbing;
    transform: scale(0.97);
}

.admin-mosaic-item.is-drag-over {
    border-color: var(--accent) !important;
    box-shadow: 0 0 20px var(--accent-glow) !important;
    transform: scale(1.02);
}

.admin-mosaic-item.is-cover {
    border-color: var(--accent);
}

.admin-mosaic-item.is-highlighted {
    box-shadow: 0 0 15px rgba(99, 102, 241, 0.3);
}

.admin-mosaic-img-wrap {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: var(--bg-deep);
}

.admin-mosaic-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
    transition: transform 0.4s ease;
}

.admin-mosaic-item:hover .admin-mosaic-img-wrap img {
    transform: scale(1.02);
}

/* Top bar with drag handle and badges */
.admin-mosaic-topbar {
    position: absolute;
    top: 8px;
    left: 8px;
    right: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 5;
    pointer-events: none;
}

.admin-mosaic-badge-order {
    background: rgba(8, 9, 26, 0.85);
    backdrop-filter: blur(8px);
    color: white;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.2rem 0.5rem;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    gap: 4px;
}

.drag-handle {
    opacity: 0.6;
    font-size: 0.85rem;
}

.admin-mosaic-top-actions {
    display: flex;
    gap: 4px;
    pointer-events: auto;
}

.badge-toggle {
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(8, 9, 26, 0.8);
    backdrop-filter: blur(8px);
    color: var(--text-muted);
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.2rem 0.55rem;
    border-radius: var(--radius-full);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.badge-toggle:hover {
    color: white;
    border-color: rgba(255, 255, 255, 0.3);
}

.badge-toggle.active {
    background: var(--accent);
    color: white;
    border-color: var(--accent);
    box-shadow: 0 0 10px var(--accent-glow);
}

/* Bottom bar with format selector & actions (visible on hover) */
.admin-mosaic-bottombar {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 6px;
    background: linear-gradient(to top, rgba(8, 9, 26, 0.95) 0%, rgba(8, 9, 26, 0.8) 75%, transparent 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 4px;
    z-index: 5;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease;
}

.admin-mosaic-item:hover .admin-mosaic-bottombar {
    opacity: 1;
    pointer-events: auto;
}

/* Format buttons */
.format-buttons-group {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.fmt-btn {
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(6px);
    color: var(--text-secondary);
    font-size: 0.68rem;
    font-weight: 600;
    padding: 0.2rem 0.45rem;
    border-radius: var(--radius-sm);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 3px;
    transition: all var(--transition-fast);
}

.fmt-btn:hover {
    background: rgba(255, 255, 255, 0.18);
    color: white;
}

.fmt-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary-light);
    box-shadow: 0 0 8px var(--primary-glow);
}

.admin-card-edit-btns {
    display: flex;
    gap: 4px;
    margin-left: auto;
}

.btn-icon {
    width: 28px;
    height: 28px;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(6px);
    color: white;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.btn-icon:hover {
    background: rgba(255, 255, 255, 0.2);
}

.btn-icon-danger:hover {
    background: var(--danger);
    border-color: var(--danger);
}

/* --- Compact Grid Styles --- */
.photos-admin-compact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: var(--space-md);
}

.photo-compact-card {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    cursor: grab;
    transition: all var(--transition-base);
}

.photo-compact-card:hover {
    border-color: var(--primary-glow-strong);
    box-shadow: var(--shadow-sm);
}

.photo-compact-card.is-cover {
    border-color: var(--accent);
}

.photo-compact-img {
    position: relative;
    height: 140px;
    background: var(--bg-deep);
}

.photo-compact-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-compact-order {
    position: absolute;
    top: 6px;
    left: 6px;
    background: rgba(0, 0, 0, 0.75);
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.4rem;
    border-radius: var(--radius-sm);
}

.photo-compact-badges {
    position: absolute;
    top: 6px;
    right: 6px;
    display: flex;
    gap: 3px;
}

.photo-compact-actions {
    padding: var(--space-sm);
}

/* Modal Format Selector Cards */
.format-modal-selector {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--space-sm);
    margin-top: var(--space-xs);
}

.fmt-modal-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    padding: var(--space-md);
    background: var(--bg-elevated);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    text-align: left;
    transition: all var(--transition-fast);
}

.fmt-modal-card:hover {
    border-color: var(--primary-light);
}

.fmt-modal-card.active {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.1);
    box-shadow: 0 0 12px var(--primary-glow);
}

.fmt-modal-icon {
    font-size: 1.3rem;
    margin-bottom: 4px;
}

.fmt-modal-title {
    font-weight: 700;
    font-size: 0.9rem;
    color: var(--text-color);
}

.fmt-modal-desc {
    font-size: 0.75rem;
    color: var(--text-muted);
    margin-top: 2px;
}

@media (max-width: 1024px) {
    :deep(.admin-main) > div:first-child {
        grid-template-columns: 1fr !important;
    }
}
</style>
