<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, useForm, Link, router } from '@inertiajs/vue3';
import axios from 'axios';
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

const getItemGridStyle = (photo) => {
    let c = photo.span_cols || 3;
    let r = photo.span_rows || 2;
    // Rétrocompatibilité
    if (c === 12 && r === 8) { c = 6; r = 4; }
    else if (c === 6 && r === 9) { c = 4; r = 6; }
    else if (c === 2 && r === 1) { c = 6; r = 4; }
    else if (c === 1 && r === 2) { c = 4; r = 6; }
    else if (c === 1 && r === 1) { c = 3; r = 2; }

    const style = {};
    if (photo.col_start && photo.row_start) {
        style.gridColumn = `${photo.col_start} / span ${c}`;
        style.gridRow = `${photo.row_start} / span ${r}`;
    } else {
        style.gridColumn = `span ${c}`;
        style.gridRow = `span ${r}`;
    }
    return style;
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

// Photo upload & batch processing
const uploading = ref(false);
const isDragOver = ref(false);
const uploadProgress = ref(null); // { currentBatch, totalBatches, uploadedCount, totalCount, percent, currentFileName }
const uploadError = ref(null);

const MAX_FILES_PER_BATCH = 15; // Max 15 photos per batch (PHP max_file_uploads is 20)
const MAX_BYTES_PER_BATCH = 35 * 1024 * 1024; // 35 Mo max par lot (PHP post_max_size safe)
const MAX_FILE_SIZE = 100 * 1024 * 1024; // 100 Mo max par photo

const createBatches = (files) => {
    const batches = [];
    let currentBatch = [];
    let currentBatchBytes = 0;

    for (const file of files) {
        const exceedsFileCount = currentBatch.length >= MAX_FILES_PER_BATCH;
        const exceedsByteSize = (currentBatchBytes + file.size) > MAX_BYTES_PER_BATCH && currentBatch.length > 0;

        if (exceedsFileCount || exceedsByteSize) {
            batches.push(currentBatch);
            currentBatch = [];
            currentBatchBytes = 0;
        }

        currentBatch.push(file);
        currentBatchBytes += file.size;
    }

    if (currentBatch.length > 0) {
        batches.push(currentBatch);
    }

    return batches;
};

const handleDrop = (e) => {
    isDragOver.value = false;
    const files = Array.from(e.dataTransfer.files || []);
    if (files.length > 0) {
        processUpload(files);
    }
};

const uploadPhotos = (e) => {
    const files = Array.from(e.target.files || []);
    if (files.length > 0) {
        processUpload(files);
        e.target.value = '';
    }
};

const processUpload = async (files) => {
    if (uploading.value) return;

    uploadError.value = null;

    // Filter valid image files
    const validFiles = files.filter(f => f.type ? f.type.startsWith('image/') : true);
    if (validFiles.length === 0) {
        uploadError.value = "Aucun fichier image valide sélectionné.";
        return;
    }

    // Check individual file size
    for (const file of validFiles) {
        if (file.size > MAX_FILE_SIZE) {
            uploadError.value = `Le fichier "${file.name}" dépasse la limite autorisée de 100 Mo.`;
            return;
        }
    }

    const batches = createBatches(validFiles);
    uploading.value = true;

    let uploadedCount = 0;
    const totalCount = validFiles.length;

    uploadProgress.value = {
        currentBatch: 0,
        totalBatches: batches.length,
        uploadedCount: 0,
        totalCount,
        percent: 0,
        message: 'Initialisation des envois...'
    };

    try {
        for (let i = 0; i < batches.length; i++) {
            const batch = batches[i];
            const batchNumber = i + 1;

            uploadProgress.value = {
                currentBatch: batchNumber,
                totalBatches: batches.length,
                uploadedCount,
                totalCount,
                percent: Math.round((uploadedCount / totalCount) * 100),
                message: `Envoi du lot ${batchNumber}/${batches.length} (${batch.length} photo${batch.length > 1 ? 's' : ''})...`
            };

            const formData = new FormData();
            batch.forEach(f => formData.append('photos[]', f));

            await axios.post(`/admin/projects/${props.project.id}/photos`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            uploadedCount += batch.length;
            uploadProgress.value.uploadedCount = uploadedCount;
            uploadProgress.value.percent = Math.round((uploadedCount / totalCount) * 100);
        }

        uploadProgress.value.message = 'Finalisation et actualisation...';
        uploadProgress.value.percent = 100;

        // Reload current page with Inertia to reflect newly uploaded photos and orders
        router.reload({
            preserveScroll: true,
            onFinish: () => {
                uploading.value = false;
                uploadProgress.value = null;
            }
        });
    } catch (err) {
        console.error('Batch upload error:', err);
        const serverMsg = err.response?.data?.message || err.message || 'Erreur inconnue lors du téléversement.';
        uploadError.value = `Erreur lors de l'envoi du lot : ${serverMsg}`;
        uploading.value = false;
        // Still reload to keep any photos from batches that already succeeded
        if (uploadedCount > 0) {
            router.reload({ preserveScroll: true });
        }
    }
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

    // Ajuster col_start si le nouveau format dépasse les 12 colonnes
    if (photo.col_start && (photo.col_start + format.cols - 1) > 12) {
        photo.col_start = Math.max(1, 13 - format.cols);
    }

    router.put(`/admin/projects/${props.project.id}/photos/${photo.id}`, {
        caption: photo.caption,
        instagram_tag: photo.instagram_tag,
        span_cols: format.cols,
        span_rows: format.rows,
        col_start: photo.col_start,
        row_start: photo.row_start,
        display_order: photo.display_order,
        is_highlighted: photo.is_highlighted,
        is_cover: photo.is_cover,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(updateAdminGridUnit);
        }
    });
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
        col_start: photo.col_start,
        row_start: photo.row_start,
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
        col_start: photo.col_start,
        row_start: photo.row_start,
        display_order: photo.display_order,
        is_highlighted: photo.is_highlighted,
        is_cover: photo.is_cover,
    }, { preserveScroll: true });
};

// Drag & Drop interactif en direct (style Android widget réactif)
const draggingIndex = ref(null);
const dragOverIndex = ref(null);
let originalPhotosState = null;
let hasReordered = false;

const onDragStart = (e, index) => {
    draggingIndex.value = index;
    dragOverIndex.value = index;
    hasReordered = false;
    originalPhotosState = JSON.parse(JSON.stringify(props.project.photos));
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', index);
};

// Réordonnancement en direct dès qu'on survole une photo (déplacement dynamique des éléments)
const onDragEnterItem = (e, targetIndex) => {
    e.preventDefault();
    if (draggingIndex.value === null || draggingIndex.value === targetIndex) return;

    // Déplacement dynamique immédiat dans la liste
    const photos = [...props.project.photos];
    const [moved] = photos.splice(draggingIndex.value, 1);
    photos.splice(targetIndex, 0, moved);

    // Recalculer les positions 2D instantanément
    computeGridPositions(photos);
    photos.forEach((p, idx) => p.display_order = idx);

    props.project.photos.splice(0, props.project.photos.length, ...photos);
    draggingIndex.value = targetIndex;
    dragOverIndex.value = targetIndex;
    hasReordered = true;
};

const onDragOver = (e, index) => {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
};

const onDragEnd = () => {
    if (hasReordered) {
        saveOrderToServer();
    }
    draggingIndex.value = null;
    dragOverIndex.value = null;
    originalPhotosState = null;
    hasReordered = false;
};

// Algorithme de calcul du positionnement 2D (simule le quadrillage 12 cols sans collisions)
const computeGridPositions = (photosList) => {
    const gridOccupied = {}; // key: "r,c" => true
    const isSlotAvailable = (row, col, w, h) => {
        if (col + w - 1 > 12) return false;
        for (let r = row; r < row + h; r++) {
            for (let c = col; c < col + w; c++) {
                if (gridOccupied[`${r},${c}`]) return false;
            }
        }
        return true;
    };

    const occupySlot = (row, col, w, h) => {
        for (let r = row; r < row + h; r++) {
            for (let c = col; c < col + w; c++) {
                gridOccupied[`${r},${c}`] = true;
            }
        }
    };

    photosList.forEach(photo => {
        const w = photo.span_cols || 3;
        const h = photo.span_rows || 2;
        let placed = false;
        let r = 1;

        while (!placed && r < 500) {
            for (let c = 1; c <= 12 - w + 1; c++) {
                if (isSlotAvailable(r, c, w, h)) {
                    photo.col_start = c;
                    photo.row_start = r;
                    occupySlot(r, c, w, h);
                    placed = true;
                    break;
                }
            }
            if (!placed) r++;
        }
    });
};

const saveOrderToServer = () => {
    const order = props.project.photos.map((p, i) => ({
        id: p.id,
        display_order: i,
        col_start: p.col_start,
        row_start: p.row_start,
    }));

    router.post(`/admin/projects/${props.project.id}/photos/reorder`, { order }, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(updateAdminGridUnit);
        }
    });
};

const onDrop = (e, targetIndex) => {
    e.preventDefault();
    onDragEnd();
};

// --- Sélection & Actions groupées ---
const selectedPhotoIds = ref([]);
const bulkTagInput = ref('');
const showBulkTagModal = ref(false);
const confirmBulkDelete = ref(false);

const isAllSelected = () => {
    return props.project.photos.length > 0 && selectedPhotoIds.value.length === props.project.photos.length;
};

const toggleSelectAll = () => {
    if (isAllSelected()) {
        selectedPhotoIds.value = [];
    } else {
        selectedPhotoIds.value = props.project.photos.map(p => p.id);
    }
};

const toggleSelectPhoto = (photoId) => {
    const idx = selectedPhotoIds.value.indexOf(photoId);
    if (idx > -1) {
        selectedPhotoIds.value.splice(idx, 1);
    } else {
        selectedPhotoIds.value.push(photoId);
    }
};

const isPhotoSelected = (photoId) => {
    return selectedPhotoIds.value.includes(photoId);
};

const applyBulkFormat = (fmt) => {
    if (selectedPhotoIds.value.length === 0) return;

    // Optimistic local update
    props.project.photos.forEach(p => {
        if (selectedPhotoIds.value.includes(p.id)) {
            p.span_cols = fmt.cols;
            p.span_rows = fmt.rows;
        }
    });

    router.post(`/admin/projects/${props.project.id}/photos/bulk-action`, {
        ids: selectedPhotoIds.value,
        action: 'set_format',
        span_cols: fmt.cols,
        span_rows: fmt.rows,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(updateAdminGridUnit);
        }
    });
};

const applyBulkHighlight = (status) => {
    if (selectedPhotoIds.value.length === 0) return;

    props.project.photos.forEach(p => {
        if (selectedPhotoIds.value.includes(p.id)) {
            p.is_highlighted = status;
        }
    });

    router.post(`/admin/projects/${props.project.id}/photos/bulk-action`, {
        ids: selectedPhotoIds.value,
        action: status ? 'set_highlight' : 'remove_highlight',
    }, { preserveScroll: true });
};

const applyBulkInstagramTag = () => {
    if (selectedPhotoIds.value.length === 0) return;

    props.project.photos.forEach(p => {
        if (selectedPhotoIds.value.includes(p.id)) {
            p.instagram_tag = bulkTagInput.value;
        }
    });

    router.post(`/admin/projects/${props.project.id}/photos/bulk-action`, {
        ids: selectedPhotoIds.value,
        action: 'set_instagram_tag',
        instagram_tag: bulkTagInput.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkTagModal.value = false;
            bulkTagInput.value = '';
        }
    });
};

const executeBulkDelete = () => {
    if (selectedPhotoIds.value.length === 0) return;

    router.post(`/admin/projects/${props.project.id}/photos/bulk-destroy`, {
        ids: selectedPhotoIds.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedPhotoIds.value = [];
            confirmBulkDelete.value = false;
        }
    });
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
                        <div class="flex-between" style="margin-bottom: var(--space-md); flex-wrap: wrap; gap: var(--space-xs);">
                            <h3 style="margin: 0;">Ajouter des photos</h3>
                            <span style="font-size: 0.85rem; color: var(--text-muted);">
                                Découpage automatique par lots • Aucun rognage • WebP optimisé
                            </span>
                        </div>

                        <!-- Error alert if upload failed -->
                        <div v-if="uploadError" class="upload-error-banner" style="margin-bottom: var(--space-md);">
                            <span>⚠️ {{ uploadError }}</span>
                            <button type="button" class="btn-dismiss-error" @click="uploadError = null">✕</button>
                        </div>

                        <label 
                            class="upload-zone" 
                            :class="{ active: uploading || isDragOver, disabled: uploading }"
                            @dragover.prevent="!uploading && (isDragOver = true)"
                            @dragleave.prevent="isDragOver = false"
                            @drop.prevent="!uploading && handleDrop($event)"
                        >
                            <input 
                                type="file" 
                                multiple 
                                accept="image/*" 
                                @change="uploadPhotos" 
                                :disabled="uploading"
                                style="display: none;" 
                            />
                            
                            <!-- Progress state -->
                            <div v-if="uploading && uploadProgress" class="upload-progress-box">
                                <div class="upload-progress-header">
                                    <span class="upload-spinner-icon">⏳</span>
                                    <span class="upload-progress-title">
                                        {{ uploadProgress.message }}
                                    </span>
                                </div>

                                <div class="batch-progress-bar">
                                    <div 
                                        class="batch-progress-fill" 
                                        :style="{ width: `${uploadProgress.percent}%` }"
                                    ></div>
                                </div>

                                <div class="upload-progress-footer">
                                    <span>
                                        📸 <strong>{{ uploadProgress.uploadedCount }}</strong> / {{ uploadProgress.totalCount }} photos
                                    </span>
                                    <span v-if="uploadProgress.totalBatches > 1" class="batch-badge">
                                        Lot {{ uploadProgress.currentBatch }} / {{ uploadProgress.totalBatches }}
                                    </span>
                                    <span class="batch-percent">{{ uploadProgress.percent }}%</span>
                                </div>
                            </div>

                            <!-- Idle state -->
                            <div v-else>
                                <div style="font-size: 2rem; margin-bottom: var(--space-sm);">📷</div>
                                <div style="color: var(--text-secondary); font-weight: 500;">
                                    {{ isDragOver ? 'Relâchez pour uploader !' : 'Cliquez ou glissez vos photos ici' }}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: var(--space-xs);">
                                    Glissez autant de photos que vous voulez (60+, 100+...) — Réparties automatiquement en petits paquets
                                </div>
                            </div>
                        </label>
                    </div>

                <!-- Photos Section Header & View Mode Switcher -->
                <div class="card" style="margin-bottom: var(--space-md); padding: var(--space-md) var(--space-lg);">
                    <div class="flex-between" style="flex-wrap: wrap; gap: var(--space-md);">
                        <div style="display: flex; align-items: center; gap: var(--space-md);">
                            <label class="select-all-checkbox-label" title="Tout sélectionner / désélectionner">
                                <input 
                                    type="checkbox" 
                                    class="bulk-checkbox"
                                    :checked="isAllSelected()" 
                                    @change="toggleSelectAll" 
                                />
                                <span style="font-size: 0.88rem; font-weight: 600;">Tout sélectionner</span>
                            </label>

                            <div style="border-left: 1px solid var(--border-color); padding-left: var(--space-md);">
                                <h3 style="margin: 0; font-size: 1.15rem; display: inline-block;">
                                    Photos du projet ({{ project.photos.length }})
                                </h3>
                                <span v-if="selectedPhotoIds.length > 0" class="selection-counter-badge">
                                    {{ selectedPhotoIds.length }} sélectionnée{{ selectedPhotoIds.length > 1 ? 's' : '' }}
                                </span>
                            </div>
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

                <!-- Floating / Sticky Bulk Action Toolbar -->
                <Transition name="bulk-bar">
                    <div v-if="selectedPhotoIds.length > 0" class="bulk-actions-toolbar card">
                        <div class="bulk-toolbar-content">
                            <div class="bulk-toolbar-info">
                                <span class="bulk-icon">✨</span>
                                <div>
                                    <strong>{{ selectedPhotoIds.length }}</strong> photo{{ selectedPhotoIds.length > 1 ? 's' : '' }} sélectionnée{{ selectedPhotoIds.length > 1 ? 's' : '' }}
                                </div>
                            </div>

                            <div class="bulk-toolbar-buttons">
                                <!-- Formats groupés -->
                                <div class="bulk-group">
                                    <span class="bulk-group-label">Format :</span>
                                    <button
                                        v-for="fmt in PHOTO_FORMATS"
                                        :key="fmt.id"
                                        type="button"
                                        class="bulk-btn"
                                        @click="applyBulkFormat(fmt)"
                                        :title="fmt.label"
                                    >
                                        {{ fmt.icon }} {{ fmt.shortLabel }}
                                    </button>
                                </div>

                                <div class="bulk-divider"></div>

                                <!-- Coups de cœur -->
                                <div class="bulk-group">
                                    <button type="button" class="bulk-btn" @click="applyBulkHighlight(true)" title="Ajouter à la sélection d'accueil">
                                        ★ Coup de cœur
                                    </button>
                                    <button type="button" class="bulk-btn" @click="applyBulkHighlight(false)" title="Retirer des coups de cœur">
                                        ☆ Retirer
                                    </button>
                                </div>

                                <div class="bulk-divider"></div>

                                <!-- Tag Instagram -->
                                <button type="button" class="bulk-btn" @click="showBulkTagModal = true">
                                    🏷️ Tag Instagram
                                </button>

                                <div class="bulk-divider"></div>

                                <!-- Supprimer -->
                                <button type="button" class="bulk-btn bulk-btn-danger" @click="confirmBulkDelete = true">
                                    🗑️ Supprimer ({{ selectedPhotoIds.length }})
                                </button>

                                <button type="button" class="bulk-btn-close" @click="selectedPhotoIds = []" title="Désélectionner tout">
                                    ✕
                                </button>
                            </div>
                        </div>
                    </div>
                </Transition>

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
                                    'is-highlighted': photo.is_highlighted,
                                    'is-selected': isPhotoSelected(photo.id)
                                }
                            ]"
                            :style="getItemGridStyle(photo)"
                            draggable="true"
                            @dragstart="onDragStart($event, index)"
                            @dragenter.prevent="onDragEnterItem($event, index)"
                            @dragover.prevent="onDragOver($event, index)"
                            @dragend="onDragEnd"
                            @drop.prevent="onDrop($event, index)"
                        >
                            <!-- Image avec ratio exact sans rognage -->
                            <div class="admin-mosaic-img-wrap">
                                <img
                                    :src="`/storage/${photo.thumbnail_path || photo.image_path}`"
                                    :alt="photo.caption || 'Photo'"
                                    loading="lazy"
                                />
                            </div>

                            <!-- Top bar: Selection checkbox, Order, badges, quick actions -->
                            <div class="admin-mosaic-topbar">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <label class="item-checkbox-wrap" @click.stop>
                                        <input 
                                            type="checkbox" 
                                            class="bulk-checkbox"
                                            :checked="isPhotoSelected(photo.id)" 
                                            @change="toggleSelectPhoto(photo.id)" 
                                        />
                                    </label>

                                    <div class="admin-mosaic-badge-order" :title="`Ordre #${index + 1} · Format: ${photo.span_cols >= photo.span_rows ? 'Paysage' : 'Portrait'}`">
                                        <span class="drag-handle">⠿</span> #{{ index + 1 }}
                                        <span style="opacity: 0.7; font-size: 0.7rem;">{{ photo.span_cols >= photo.span_rows ? '🖼️' : '📱' }}</span>
                                    </div>
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
                        @dragenter.prevent="onDragEnterItem($event, index)"
                        @dragover.prevent="onDragOver($event, index)"
                        @dragend="onDragEnd"
                        @drop.prevent="onDrop($event, index)"
                        :class="{
                            'is-dragging': draggingIndex === index,
                            'is-drag-over': dragOverIndex === index,
                            'is-cover': photo.is_cover,
                            'is-highlighted': photo.is_highlighted,
                            'is-selected': isPhotoSelected(photo.id)
                        }"
                    >
                        <div class="photo-compact-img">
                            <img :src="`/storage/${photo.thumbnail_path || photo.image_path}`" :alt="photo.caption || 'Photo'" />
                            <div class="photo-compact-order" style="display: flex; align-items: center; gap: 4px;">
                                <label class="item-checkbox-wrap-compact" @click.stop>
                                    <input 
                                        type="checkbox" 
                                        class="bulk-checkbox"
                                        :checked="isPhotoSelected(photo.id)" 
                                        @change="toggleSelectPhoto(photo.id)" 
                                    />
                                </label>
                                <span>#{{ index + 1 }}</span>
                            </div>
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

        <!-- Bulk Instagram Tag Modal -->
        <AdminModal :show="showBulkTagModal" @close="showBulkTagModal = false" title="Modifier le Tag Instagram groupé">
            <div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: var(--space-md);">
                    Définir le même tag Instagram pour les <strong>{{ selectedPhotoIds.length }}</strong> photos sélectionnées (ou laisser vide pour retirer le tag).
                </p>
                <div class="form-group">
                    <label class="form-label">Tag Instagram</label>
                    <input 
                        type="text" 
                        v-model="bulkTagInput" 
                        placeholder="@username ou laisser vide" 
                    />
                </div>
                <div style="display: flex; gap: var(--space-sm); justify-content: flex-end; margin-top: var(--space-lg);">
                    <button type="button" class="btn btn-ghost" @click="showBulkTagModal = false">Annuler</button>
                    <button type="button" class="btn btn-primary" @click="applyBulkInstagramTag">
                        Appliquer aux {{ selectedPhotoIds.length }} photos
                    </button>
                </div>
            </div>
        </AdminModal>

        <!-- Bulk Deletion Confirmation -->
        <ConfirmModal
            :show="confirmBulkDelete"
            title="Supprimer les photos sélectionnées"
            :message="`Êtes-vous sûr de vouloir supprimer définitivement ces ${selectedPhotoIds.length} photos ? Cette action est irréversible.`"
            confirmText="Supprimer tout"
            @close="confirmBulkDelete = false"
            @confirm="executeBulkDelete"
        />

        <!-- Deletion Confirmation Single -->
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
    opacity: 0.35;
    cursor: grabbing;
    transform: scale(0.96);
    filter: brightness(0.8);
    box-shadow: 0 0 0 2px var(--primary), 0 10px 30px rgba(0, 0, 0, 0.5);
    z-index: 100;
}

.admin-mosaic-item.is-drag-over {
    border-color: var(--primary-light) !important;
    box-shadow: 0 0 25px var(--primary-glow-strong) !important;
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

/* --- Batch Upload Progress & Feedback --- */
.upload-zone.disabled {
    cursor: not-allowed;
    pointer-events: none;
    opacity: 0.9;
}

.upload-error-banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-md);
    background: rgba(239, 68, 68, 0.12);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: var(--space-sm) var(--space-md);
    border-radius: var(--radius-md);
    font-size: 0.88rem;
}

.btn-dismiss-error {
    background: none;
    border: none;
    color: #fca5a5;
    font-size: 1.1rem;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
}

.upload-progress-box {
    width: 100%;
    max-width: 480px;
    margin: 0 auto;
    padding: var(--space-sm) 0;
}

.upload-progress-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-sm);
    margin-bottom: var(--space-md);
}

.upload-spinner-icon {
    font-size: 1.3rem;
    animation: pulse 1.5s infinite;
}

.upload-progress-title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--accent);
}

.batch-progress-bar {
    width: 100%;
    height: 10px;
    background: var(--bg-deep);
    border-radius: var(--radius-full);
    overflow: hidden;
    border: 1px solid var(--border-color);
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.5);
    margin-bottom: var(--space-sm);
}

.batch-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    border-radius: var(--radius-full);
    transition: width 0.3s ease;
    box-shadow: 0 0 10px var(--accent-glow);
}

.upload-progress-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.82rem;
    color: var(--text-muted);
}

.batch-badge {
    background: rgba(99, 102, 241, 0.18);
    color: var(--primary-light);
    border: 1px solid rgba(99, 102, 241, 0.3);
    padding: 2px 8px;
    border-radius: var(--radius-full);
    font-weight: 600;
    font-size: 0.78rem;
}

.batch-percent {
    font-weight: 700;
    color: var(--text-color);
}

/* --- Sélection & Barre d'actions groupées --- */
.select-all-checkbox-label {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
    color: var(--text-color);
}

.selection-counter-badge {
    display: inline-block;
    margin-left: var(--space-sm);
    font-size: 0.78rem;
    font-weight: 700;
    padding: 0.18rem 0.6rem;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    color: white;
    border-radius: var(--radius-full);
    box-shadow: 0 2px 8px var(--primary-glow);
}

.bulk-checkbox {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
    cursor: pointer;
    border-radius: 4px;
}

.item-checkbox-wrap {
    display: flex;
    align-items: center;
    background: rgba(0, 0, 0, 0.75);
    padding: 3px 6px;
    border-radius: var(--radius-sm);
    backdrop-filter: blur(4px);
    cursor: pointer;
}

.item-checkbox-wrap-compact {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
}

.admin-mosaic-item.is-selected {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 2px var(--primary), 0 8px 30px rgba(99, 102, 241, 0.4) !important;
}

.photo-compact-card.is-selected {
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 2px var(--primary) !important;
}

.bulk-actions-toolbar {
    position: sticky;
    top: calc(var(--nav-height) + 16px);
    z-index: 100;
    background: rgba(16, 20, 38, 0.95);
    backdrop-filter: blur(20px);
    border: 1px solid var(--primary-light);
    border-radius: var(--radius-lg);
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5), 0 0 20px var(--primary-glow);
    padding: var(--space-sm) var(--space-lg);
    margin-bottom: var(--space-lg);
}

.bulk-toolbar-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: var(--space-md);
}

.bulk-toolbar-info {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    font-size: 0.95rem;
    color: var(--text-color);
}

.bulk-icon {
    font-size: 1.2rem;
}

.bulk-toolbar-buttons {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
}

.bulk-group {
    display: flex;
    align-items: center;
    gap: 4px;
}

.bulk-group-label {
    font-size: 0.78rem;
    color: var(--text-muted);
    font-weight: 600;
    margin-right: 4px;
}

.bulk-divider {
    width: 1px;
    height: 24px;
    background: var(--border-color);
    margin: 0 4px;
}

.bulk-btn {
    border: 1px solid var(--border-color);
    background: var(--bg-deep);
    color: var(--text-color);
    padding: 0.35rem 0.65rem;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.bulk-btn:hover {
    background: var(--surface-color);
    border-color: var(--primary-light);
    color: white;
}

.bulk-btn-danger {
    background: rgba(239, 68, 68, 0.15);
    border-color: rgba(239, 68, 68, 0.35);
    color: #fca5a5;
}

.bulk-btn-danger:hover {
    background: var(--danger);
    color: white;
    border-color: var(--danger);
}

.bulk-btn-close {
    background: none;
    border: none;
    color: var(--text-muted);
    font-size: 1.1rem;
    cursor: pointer;
    padding: 0 6px;
    line-height: 1;
}

.bulk-btn-close:hover {
    color: white;
}

/* Transitions Toolbar */
.bulk-bar-enter-active,
.bulk-bar-leave-active {
    transition: all 0.3s ease;
}

.bulk-bar-enter-from,
.bulk-bar-leave-to {
    opacity: 0;
    transform: translateY(-15px);
}

@media (max-width: 1024px) {
    :deep(.admin-main) > div:first-child {
        grid-template-columns: 1fr !important;
    }
}
</style>
