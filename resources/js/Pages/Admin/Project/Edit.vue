<script setup>
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminModal from '@/Components/AdminModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps({
    project: Object,
    categories: Array,
});

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
const uploadPhotos = (e) => {
    const files = Array.from(e.target.files);
    if (files.length === 0) return;

    const formData = new FormData();
    files.forEach(f => formData.append('photos[]', f));

    uploading.value = true;
    router.post(`/admin/projects/${props.project.id}/photos`, formData, {
        forceFormData: true,
        preserveScroll: true,
        onFinish: () => {
            uploading.value = false;
            e.target.value = '';
        },
    });
};

// Photo editing modal
const editingPhoto = ref(null);
const photoForm = useForm({
    caption: '',
    instagram_tag: '',
    span_cols: 1,
    span_rows: 1,
    display_order: 0,
    is_highlighted: false,
    is_cover: false,
});

const openPhotoEdit = (photo) => {
    editingPhoto.value = photo;
    photoForm.caption = photo.caption || '';
    photoForm.instagram_tag = photo.instagram_tag || '';
    photoForm.span_cols = photo.span_cols;
    photoForm.span_rows = photo.span_rows;
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

// Quick grid size change
const quickSetSize = (photo, cols, rows) => {
    router.put(`/admin/projects/${props.project.id}/photos/${photo.id}`, {
        caption: photo.caption,
        instagram_tag: photo.instagram_tag,
        span_cols: cols,
        span_rows: rows,
        display_order: photo.display_order,
        is_highlighted: photo.is_highlighted,
        is_cover: photo.is_cover,
    }, { preserveScroll: true });
};

// Drag & Drop reorder
const dragging = ref(null);

const onDragStart = (e, index) => {
    dragging.value = index;
    e.dataTransfer.effectAllowed = 'move';
};

const onDragOver = (e, index) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
};

const onDrop = (e, targetIndex) => {
    e.preventDefault();
    if (dragging.value === null || dragging.value === targetIndex) return;

    const photos = [...props.project.photos];
    const [moved] = photos.splice(dragging.value, 1);
    photos.splice(targetIndex, 0, moved);

    const order = photos.map((p, i) => ({ id: p.id, display_order: i }));

    router.post(`/admin/projects/${props.project.id}/photos/reorder`, { order }, {
        preserveScroll: true,
    });

    dragging.value = null;
};
</script>

<template>
    <Head :title="`Éditer: ${project.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2>{{ project.title }}</h2>
            <Link href="/admin/projects" class="btn btn-ghost btn-sm">← Projets</Link>
        </template>

        <div style="display: grid; grid-template-columns: 350px 1fr; gap: var(--space-xl);">
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
                            <select v-model="form.category_id" required>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
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
                            <div>
                                <label class="form-label">Statut</label>
                                <select v-model="form.status" style="padding: 0.4rem;">
                                    <option value="published">Publié</option>
                                    <option value="draft">Brouillon</option>
                                </select>
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
                        <h3 style="margin: 0;">Photos ({{ project.photos.length }})</h3>
                    </div>
                    <label class="upload-zone" :class="{ active: uploading }">
                        <input type="file" multiple accept="image/*" @change="uploadPhotos" style="display: none;" />
                        <div v-if="uploading" style="color: var(--accent);">
                            ⏳ Téléversement et optimisation en cours...
                        </div>
                        <div v-else>
                            <div style="font-size: 2rem; margin-bottom: var(--space-sm);">📷</div>
                            <div style="color: var(--text-secondary);">Cliquez ou glissez vos photos ici</div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: var(--space-xs);">
                                Les thumbnails WebP sont générés automatiquement
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Photos Grid (draggable) -->
                <div class="photos-admin-grid" v-if="project.photos.length > 0">
                    <div
                        v-for="(photo, index) in project.photos"
                        :key="photo.id"
                        class="photo-admin-card"
                        draggable="true"
                        @dragstart="onDragStart($event, index)"
                        @dragover="onDragOver($event, index)"
                        @drop="onDrop($event, index)"
                        :class="{ 'is-cover': photo.is_cover, 'is-highlighted': photo.is_highlighted }"
                    >
                        <div class="photo-admin-img">
                            <img :src="`/storage/${photo.thumbnail_path || photo.image_path}`" :alt="photo.caption || 'Photo'" />
                            <div class="photo-admin-order">#{{ index + 1 }}</div>
                            <div class="photo-admin-badges">
                                <span v-if="photo.is_cover" class="badge badge-accent" style="font-size: 0.65rem;">Cover</span>
                                <span v-if="photo.is_highlighted" class="badge badge-primary" style="font-size: 0.65rem;">★</span>
                            </div>
                        </div>

                        <div class="photo-admin-actions">
                            <!-- Quick size buttons -->
                            <div class="size-grid">
                                <button
                                    v-for="size in [{c:1,r:1,l:'1×1'},{c:2,r:1,l:'2×1'},{c:1,r:2,l:'1×2'},{c:2,r:2,l:'2×2'}]"
                                    :key="size.l"
                                    class="size-btn"
                                    :class="{ active: photo.span_cols === size.c && photo.span_rows === size.r }"
                                    @click="quickSetSize(photo, size.c, size.r)"
                                >
                                    {{ size.l }}
                                </button>
                            </div>

                            <div class="photo-admin-btns">
                                <button class="btn btn-ghost btn-sm" @click="openPhotoEdit(photo)">✏️</button>
                                <button class="btn btn-danger btn-sm" @click="deletePhoto(photo.id)">🗑️</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="empty-state">
                    <div class="empty-state-icon">🖼️</div>
                    <div class="empty-state-title">Aucune photo</div>
                    <div class="empty-state-text">Utilisez la zone ci-dessus pour ajouter des photos à ce shooting.</div>
                </div>
            </div>
        </div>

        <!-- Photo Edit Modal -->
        <AdminModal :show="!!editingPhoto" @close="editingPhoto = null" title="Modifier la photo">
            <form v-if="editingPhoto" @submit.prevent="savePhoto">
                <div style="margin-bottom: var(--space-lg); text-align: center;">
                    <img :src="`/storage/${editingPhoto.thumbnail_path || editingPhoto.image_path}`" style="max-height: 200px; border-radius: var(--radius-md);" />
                </div>

                <div class="form-group">
                    <label class="form-label">Légende</label>
                    <input type="text" v-model="photoForm.caption" placeholder="Description de la photo..." />
                </div>

                <div class="form-group">
                    <label class="form-label">Tag Instagram</label>
                    <input type="text" v-model="photoForm.instagram_tag" placeholder="@username" />
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Colonnes</label>
                        <select v-model="photoForm.span_cols">
                            <option :value="1">1 (Normal)</option>
                            <option :value="2">2 (Large)</option>
                            <option :value="3">3 (Très large)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lignes</label>
                        <select v-model="photoForm.span_rows">
                            <option :value="1">1 (Normal)</option>
                            <option :value="2">2 (Haute)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ordre</label>
                        <input type="number" v-model="photoForm.display_order" />
                    </div>
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
.photos-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: var(--space-md);
}

.photo-admin-card {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow: hidden;
    cursor: grab;
    transition: all var(--transition-base);
}

.photo-admin-card:hover {
    border-color: var(--primary-glow-strong);
    box-shadow: var(--shadow-sm);
}

.photo-admin-card.is-cover {
    border-color: var(--accent);
    box-shadow: 0 0 12px var(--accent-glow);
}

.photo-admin-card.is-highlighted {
    border-color: var(--primary);
}

.photo-admin-card:active {
    cursor: grabbing;
    opacity: 0.7;
}

.photo-admin-img {
    position: relative;
    height: 150px;
}

.photo-admin-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-admin-order {
    position: absolute;
    top: var(--space-sm);
    left: var(--space-sm);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 0.7rem;
    font-weight: 700;
    padding: 0.15rem 0.4rem;
    border-radius: var(--radius-sm);
}

.photo-admin-badges {
    position: absolute;
    top: var(--space-sm);
    right: var(--space-sm);
    display: flex;
    gap: 0.2rem;
}

.photo-admin-actions {
    padding: var(--space-sm);
}

.size-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 2px;
    margin-bottom: var(--space-sm);
}

.size-btn {
    padding: 0.25rem;
    font-size: 0.7rem;
    background: var(--bg-elevated);
    color: var(--text-muted);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all var(--transition-fast);
}

.size-btn:hover {
    border-color: var(--primary);
    color: var(--text-color);
}

.size-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.photo-admin-btns {
    display: flex;
    gap: var(--space-xs);
    justify-content: flex-end;
}

@media (max-width: 1024px) {
    :deep(.admin-main) > div:first-child {
        grid-template-columns: 1fr !important;
    }
}
</style>
