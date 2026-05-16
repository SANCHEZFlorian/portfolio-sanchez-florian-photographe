<script setup>
import { ref } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminModal from '@/Components/AdminModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';

const props = defineProps({
    photos: Array,
    categories: Array,
});

// Photo upload
const uploading = ref(false);
const isDragOver = ref(false);
const uploadForm = useForm({
    photos: [],
    category_id: props.categories.length > 0 ? props.categories[0].id : null,
});

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
    if (uploadForm.category_id) {
        formData.append('category_id', uploadForm.category_id);
    }

    uploading.value = true;
    router.post('/admin/photos', formData, {
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
    category_id: null,
    span_cols: 1,
    span_rows: 1,
    display_order: 0,
    is_highlighted: false,
});

const openPhotoEdit = (photo) => {
    editingPhoto.value = photo;
    photoForm.caption = photo.caption || '';
    photoForm.instagram_tag = photo.instagram_tag || '';
    photoForm.category_id = photo.category_id;
    photoForm.span_cols = photo.span_cols;
    photoForm.span_rows = photo.span_rows;
    photoForm.display_order = photo.display_order;
    photoForm.is_highlighted = photo.is_highlighted || false;
};

const savePhoto = () => {
    photoForm.put(`/admin/photos/${editingPhoto.value.id}`, {
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
    router.delete(`/admin/photos/${photoId}`, {
        preserveScroll: true,
        onSuccess: () => { confirmDeletion.value = null; },
    });
};

// Quick grid size change
const quickSetSize = (photo, cols, rows) => {
    router.put(`/admin/photos/${photo.id}`, {
        ...photo,
        span_cols: cols,
        span_rows: rows,
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

    const photos = [...props.photos];
    const [moved] = photos.splice(dragging.value, 1);
    photos.splice(targetIndex, 0, moved);

    const order = photos.map((p, i) => ({ id: p.id, display_order: i }));

    router.post('/admin/photos/reorder', { order }, {
        preserveScroll: true,
    });

    dragging.value = null;
};
</script>

<template>
    <Head title="Photos en vrac" />

    <AuthenticatedLayout>
        <template #header>
            <h2>Photos en vrac</h2>
            <div style="font-size: 0.9rem; color: var(--text-muted);">Gestion de la galerie autonome</div>
        </template>

        <div class="card" style="margin-bottom: var(--space-xl);">
            <div style="display: grid; grid-template-columns: 1fr 300px; gap: var(--space-xl); align-items: start;">
                <div>
                    <h3 style="margin-bottom: var(--space-sm);">Importer des photos</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: var(--space-md);">
                        Ces photos apparaîtront dans la section "Galerie" du site.
                    </p>
                    
                    <label 
                        class="upload-zone" 
                        :class="{ active: uploading || isDragOver }"
                        @dragover.prevent="isDragOver = true"
                        @dragleave.prevent="isDragOver = false"
                        @drop.prevent="handleDrop"
                    >
                        <input type="file" multiple accept="image/*" @change="uploadPhotos" style="display: none;" />
                        <div v-if="uploading" style="color: var(--accent);">
                            ⏳ Téléversement et optimisation...
                        </div>
                        <div v-else>
                            <div style="font-size: 2rem; margin-bottom: var(--space-sm);">📷</div>
                            <div>{{ isDragOver ? 'Relâchez pour uploader !' : 'Cliquez ou glissez pour uploader' }}</div>
                        </div>
                    </label>
                </div>

                <div class="card" style="background: var(--bg-elevated); border-style: dashed;">
                    <h4 style="margin-bottom: var(--space-sm);">Catégorie par défaut</h4>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: var(--space-md);">
                        S'appliquera aux prochaines photos uploadées.
                    </p>
                    <select v-model="uploadForm.category_id" style="width: 100%;">
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Photos Grid -->
        <div v-if="photos.length > 0">
            <div class="flex-between" style="margin-bottom: var(--space-lg);">
                <h3 style="margin: 0;">Galerie ({{ photos.length }} photos)</h3>
                <div style="font-size: 0.85rem; color: var(--text-muted);">Glissez les photos pour réorganiser l'ordre</div>
            </div>
            
            <div class="photos-admin-grid">
                <div 
                    v-for="(photo, index) in photos" 
                    :key="photo.id"
                    class="photo-admin-card"
                    draggable="true"
                    @dragstart="onDragStart($event, index)"
                    @dragover="onDragOver($event, index)"
                    @drop="onDrop($event, index)"
                    :class="{ 'is-highlighted': photo.is_highlighted }"
                >
                    <div class="photo-admin-img">
                        <img :src="`/storage/${photo.thumbnail_path || photo.image_path}`" :alt="photo.caption || 'Photo'" />
                        <div class="photo-admin-order">#{{ index + 1 }}</div>
                        <div class="photo-admin-badges">
                            <span v-if="photo.category" class="badge badge-primary" style="font-size: 0.65rem;">{{ photo.category.name }}</span>
                            <span v-if="photo.is_highlighted" class="badge badge-accent" style="font-size: 0.65rem;">★</span>
                        </div>
                    </div>

                    <div class="photo-admin-actions">
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
        </div>

        <div v-else class="empty-state">
            <div class="empty-state-icon">🖼️</div>
            <div class="empty-state-title">La galerie est vide</div>
            <div class="empty-state-text">Commencez par ajouter des photos à l'aide de la zone d'importation.</div>
        </div>

        <!-- Edit Modal -->
        <AdminModal :show="!!editingPhoto" @close="editingPhoto = null" title="Propriétés de la photo">
            <form v-if="editingPhoto" @submit.prevent="savePhoto">
                <div style="margin-bottom: var(--space-lg); text-align: center;">
                    <img :src="`/storage/${editingPhoto.thumbnail_path || editingPhoto.image_path}`" style="max-height: 200px; border-radius: var(--radius-md);" />
                </div>

                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <select v-model="photoForm.category_id">
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Légende</label>
                    <input type="text" v-model="photoForm.caption" />
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Tag Instagram</label>
                        <input type="text" v-model="photoForm.instagram_tag" placeholder="@username" />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ordre d'affichage</label>
                        <input type="number" v-model="photoForm.display_order" />
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                    <div class="form-group">
                        <label class="form-label">Colonnes (Grille)</label>
                        <select v-model="photoForm.span_cols">
                            <option :value="1">1 (Normal)</option>
                            <option :value="2">2 (Large)</option>
                            <option :value="3">3 (Extra)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lignes (Grille)</label>
                        <select v-model="photoForm.span_rows">
                            <option :value="1">1 (Normal)</option>
                            <option :value="2">2 (Haut)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: var(--space-xl);">
                    <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer;">
                        <input type="checkbox" v-model="photoForm.is_highlighted" style="width: auto; accent-color: var(--primary);" />
                        Mettre en avant (Favori)
                    </label>
                </div>

                <div style="display: flex; gap: var(--space-sm); justify-content: flex-end;">
                    <button type="button" class="btn btn-ghost" @click="editingPhoto = null">Annuler</button>
                    <button type="submit" class="btn btn-primary" :disabled="photoForm.processing">
                        {{ photoForm.processing ? 'Enregistrement...' : 'Enregistrer' }}
                    </button>
                </div>
            </form>
        </AdminModal>

        <!-- Deletion Confirmation -->
        <ConfirmModal
            :show="!!confirmDeletion"
            title="Supprimer la photo"
            message="Supprimer définitivement cette photo de la galerie ?"
            @close="confirmDeletion = null"
            @confirm="executeDelete"
        />
    </AuthenticatedLayout>
</template>

<style scoped>
.upload-zone {
    display: block;
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--space-2xl);
    text-align: center;
    cursor: pointer;
    transition: all var(--transition-base);
    background: var(--surface-color);
}

.upload-zone:hover, .upload-zone.active {
    border-color: var(--primary);
    background: var(--primary-glow);
}

.photos-admin-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
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
    border-color: var(--primary);
    box-shadow: var(--shadow-sm);
}

.photo-admin-card.is-highlighted {
    border-color: var(--accent);
    box-shadow: 0 0 10px var(--accent-glow);
}

.photo-admin-img {
    height: 160px;
    position: relative;
}

.photo-admin-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.photo-admin-order {
    position: absolute;
    top: var(--space-xs);
    left: var(--space-xs);
    background: rgba(0,0,0,0.7);
    color: white;
    font-size: 0.7rem;
    padding: 0.1rem 0.4rem;
    border-radius: var(--radius-sm);
}

.photo-admin-badges {
    position: absolute;
    top: var(--space-xs);
    right: var(--space-xs);
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
    font-size: 0.65rem;
    padding: 0.2rem;
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    color: var(--text-muted);
    cursor: pointer;
    border-radius: var(--radius-xs);
}

.size-btn.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.photo-admin-btns {
    display: flex;
    justify-content: flex-end;
    gap: var(--space-xs);
}
</style>
