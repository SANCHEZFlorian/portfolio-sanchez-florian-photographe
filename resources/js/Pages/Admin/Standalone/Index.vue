<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AdminModal from '@/Components/AdminModal.vue';
import ConfirmModal from '@/Components/ConfirmModal.vue';
import PremiumSelect from '@/Components/PremiumSelect.vue';
import {
    PhEye,
    PhCamera,
    PhUploadSimple,
    PhPalette,
    PhSparkle,
    PhSquaresFour,
    PhRows,
    PhCompass,
    PhTag,
    PhInstagramLogo,
    PhTrash,
    PhPencilSimple,
    PhImages,
} from '@phosphor-icons/vue';

const props = defineProps({
    photos: Array,
    categories: Array,
});

// Mode d'affichage de la gestion des photos : 'mosaic' ou 'compact'
const viewMode = ref('mosaic');

// Formats photographiques disponibles (tailles divisées par deux)
const PHOTO_FORMATS = [
    {
        id: 'landscape-sm',
        cols: 3,
        rows: 2,
        label: 'Petit Paysage',
        shortLabel: 'P. Paysage',
        icon: '3:2',
        desc: 'Ratio 3:2 — Quart de largeur (4 par rangée)',
    },
    {
        id: 'landscape-lg',
        cols: 6,
        rows: 4,
        label: 'Grand Paysage',
        shortLabel: 'G. Paysage',
        icon: '3:2',
        desc: 'Ratio 3:2 — Moitié de largeur (2 par rangée)',
    },
    {
        id: 'portrait-sm',
        cols: 2,
        rows: 3,
        label: 'Petit Portrait',
        shortLabel: 'P. Portrait',
        icon: '2:3',
        desc: 'Ratio 2:3 — Un sixième de largeur (6 par rangée)',
    },
    {
        id: 'portrait-lg',
        cols: 4,
        rows: 6,
        label: 'Grand Portrait',
        shortLabel: 'G. Portrait',
        icon: '2:3',
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
    if (c === 12 && r === 8) return 'landscape-lg';
    if (c === 6 && r === 9) return 'portrait-lg';
    return c >= r ? 'landscape-sm' : 'portrait-sm';
};



// Photo upload & batch processing
const uploading = ref(false);
const isDragOver = ref(false);
const uploadProgress = ref(null); // { currentBatch, totalBatches, uploadedCount, totalCount, percent, message }
const uploadError = ref(null);
const uploadForm = useForm({
    photos: [],
    category_id: props.categories.length > 0 ? props.categories[0].id : null,
});

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
            if (uploadForm.category_id) {
                formData.append('category_id', uploadForm.category_id);
            }

            await axios.post('/admin/photos', formData, {
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
    category_id: null,
    span_cols: 3,
    span_rows: 2,
    display_order: 0,
    is_highlighted: false,
});

const openPhotoEdit = (photo) => {
    editingPhoto.value = photo;
    photoForm.caption = photo.caption || '';
    photoForm.instagram_tag = photo.instagram_tag || '';
    photoForm.category_id = photo.category_id;
    photoForm.span_cols = photo.span_cols || 3;
    photoForm.span_rows = photo.span_rows || 2;
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
        onSuccess: () => {
            compositionRows.value.forEach(r => {
                r.slots.forEach(s => {
                    if (s.photo && s.photo.id === photoId) s.photo = null;
                });
            });
            confirmDeletion.value = null;
        },
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

    router.put(`/admin/photos/${photo.id}`, {
        caption: photo.caption,
        instagram_tag: photo.instagram_tag,
        category_id: photo.category_id,
        span_cols: format.cols,
        span_rows: format.rows,
        col_start: photo.col_start,
        row_start: photo.row_start,
        display_order: photo.display_order,
        is_highlighted: photo.is_highlighted,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(updateAdminGridUnit);
        }
    });
};

// Quick toggle Highlight
const toggleHighlight = (photo) => {
    photo.is_highlighted = !photo.is_highlighted;

    router.put(`/admin/photos/${photo.id}`, {
        caption: photo.caption,
        instagram_tag: photo.instagram_tag,
        category_id: photo.category_id,
        span_cols: photo.span_cols,
        span_rows: photo.span_rows,
        col_start: photo.col_start,
        row_start: photo.row_start,
        display_order: photo.display_order,
        is_highlighted: photo.is_highlighted,
    }, { preserveScroll: true });
};

// --- Architecture Lignes Harmoniques & Slots (Emplacements) ---
const compositionRows = ref([]);
const showAddRowModal = ref(false);
const addRowTargetIndex = ref(null);

// Drag & drop state
const draggedItem = ref(null); // { photo, fromSlot, fromRowIndex, fromDock }
const dragOverSlot = ref(null);
const draggingRowIndex = ref(null);
const dragOverRowIndex = ref(null);

const getFormatLabel = (cols, rows) => {
    if (cols === 6 && rows === 4) return 'Grand Paysage';
    if (cols === 3 && rows === 2) return 'Petit Paysage';
    if (cols === 4 && rows === 6) return 'Grand Portrait';
    if (cols === 2 && rows === 3) return 'Petit Portrait';
    return `${cols}×${rows}`;
};

// Bac de photos non placées (en attente)
const unplacedPhotos = computed(() => {
    const placedIds = new Set();
    compositionRows.value.forEach(row => {
        row.slots.forEach(slot => {
            if (slot.photo) placedIds.add(slot.photo.id);
        });
    });
    return props.photos.filter(p => !placedIds.has(p.id));
});

const HARMONY_TEMPLATES = [
    {
        id: 'quatuor-landscapes',
        title: '4 Petits Paysages',
        photosCount: 4,
        totalRows: 2,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 4, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 7, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 10, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
        ]
    },
    {
        id: 'sextuor-portraits',
        title: '6 Petits Portraits',
        photosCount: 6,
        totalRows: 3,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 3, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 5, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 7, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 9, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 11, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
        ]
    },
    {
        id: 'duo-landscapes',
        title: '2 Grands Paysages',
        photosCount: 2,
        totalRows: 4,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 6, span_rows: 4, label: 'Grand Paysage' },
            { col_start: 7, row_start: 1, span_cols: 6, span_rows: 4, label: 'Grand Paysage' },
        ]
    },
    {
        id: 'trio-portraits',
        title: '3 Grands Portraits',
        photosCount: 3,
        totalRows: 6,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 4, span_rows: 6, label: 'Grand Portrait' },
            { col_start: 5, row_start: 1, span_cols: 4, span_rows: 6, label: 'Grand Portrait' },
            { col_start: 9, row_start: 1, span_cols: 4, span_rows: 6, label: 'Grand Portrait' },
        ]
    },
    {
        id: 'one-lg-landscape-four-sm',
        title: '1 G. Paysage + 4 P. Paysages',
        photosCount: 5,
        totalRows: 4,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 6, span_rows: 4, label: 'Grand Paysage' },
            { col_start: 7, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 7, row_start: 3, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 10, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 10, row_start: 3, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
        ]
    },
    {
        id: 'lg-landscape-lg-portrait-mix',
        title: '1 G. Paysage + 1 G. Portrait + 2 P. Paysages + 2 P. Portraits',
        photosCount: 6,
        totalRows: 6,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 6, span_rows: 4, label: 'Grand Paysage' },
            { col_start: 1, row_start: 5, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 4, row_start: 5, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 7, row_start: 1, span_cols: 4, span_rows: 6, label: 'Grand Portrait' },
            { col_start: 11, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 11, row_start: 4, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
        ]
    },
    {
        id: 'four-sm-landscapes-six-sm-portraits',
        title: '4 Petits Paysages + 6 Petits Portraits',
        photosCount: 10,
        totalRows: 5,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 4, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 1, row_start: 3, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 3, row_start: 3, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 5, row_start: 3, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 7, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 9, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 11, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 7, row_start: 4, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 10, row_start: 4, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
        ]
    },
    {
        id: 'two-lg-landscapes-two-sm-six-portraits',
        title: '2 G. Paysages + 2 P. Paysages + 6 P. Portraits',
        photosCount: 10,
        totalRows: 8,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 4, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 7, row_start: 1, span_cols: 6, span_rows: 4, label: 'Grand Paysage' },
            { col_start: 1, row_start: 3, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 3, row_start: 3, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 5, row_start: 3, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 7, row_start: 5, span_cols: 6, span_rows: 4, label: 'Grand Paysage' },
            { col_start: 1, row_start: 6, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 3, row_start: 6, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 5, row_start: 6, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
        ]
    },
    {
        id: 'two-lg-landscapes-two-sm-one-lg-port-two-sm',
        title: '2 G. Paysages + 2 P. Paysages + 1 G. Portrait + 2 P. Portraits',
        photosCount: 7,
        totalRows: 8,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 4, row_start: 1, span_cols: 3, span_rows: 2, label: 'Petit Paysage' },
            { col_start: 7, row_start: 1, span_cols: 6, span_rows: 4, label: 'Grand Paysage' },
            { col_start: 1, row_start: 3, span_cols: 4, span_rows: 6, label: 'Grand Portrait' },
            { col_start: 5, row_start: 3, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 5, row_start: 6, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 7, row_start: 5, span_cols: 6, span_rows: 4, label: 'Grand Paysage' },
        ]
    },
    {
        id: 'one-lg-portrait-eight-sm',
        title: '1 Grand Portrait + 8 Petits Portraits',
        photosCount: 9,
        totalRows: 6,
        slots: [
            { col_start: 1, row_start: 1, span_cols: 4, span_rows: 6, label: 'Grand Portrait' },
            { col_start: 5, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 7, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 9, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 11, row_start: 1, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 5, row_start: 4, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 7, row_start: 4, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 9, row_start: 4, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
            { col_start: 11, row_start: 4, span_cols: 2, span_rows: 3, label: 'Petit Portrait' },
        ]
    }
];

// Initialisation des lignes et slots à partir des photos existantes
const initCompositionRows = () => {
    const photos = props.photos || [];
    if (photos.length === 0) {
        compositionRows.value = [];
        return;
    }

    const placed = photos.filter(p => p.col_start && p.row_start);
    if (placed.length === 0) {
        compositionRows.value = [];
        return;
    }

    const sorted = [...placed].sort((a, b) => {
        if (a.row_start !== b.row_start) return a.row_start - b.row_start;
        return a.col_start - b.col_start;
    });

    const blocks = [];
    let currentBlock = null;

    sorted.forEach(photo => {
        const rStart = photo.row_start;
        const h = photo.span_rows || 2;
        const rEnd = rStart + h - 1;

        if (!currentBlock) {
            currentBlock = {
                id: `row-${Date.now()}-${rStart}`,
                startRow: rStart,
                endRow: rEnd,
                photos: [photo]
            };
        } else {
            if (rStart <= currentBlock.endRow) {
                currentBlock.photos.push(photo);
                if (rEnd > currentBlock.endRow) {
                    currentBlock.endRow = rEnd;
                }
            } else {
                blocks.push(currentBlock);
                currentBlock = {
                    id: `row-${Date.now()}-${rStart}`,
                    startRow: rStart,
                    endRow: rEnd,
                    photos: [photo]
                };
            }
        }
    });
    if (currentBlock) blocks.push(currentBlock);

    compositionRows.value = blocks.map((block, bIdx) => {
        const minR = block.startRow;
        const totalRows = Math.max(2, block.endRow - block.startRow + 1);

        const slots = block.photos.map(p => ({
            id: `slot-${bIdx}-${p.id}`,
            col_start: p.col_start,
            row_start: p.row_start - minR + 1,
            span_cols: p.span_cols || 3,
            span_rows: p.span_rows || 2,
            label: getFormatLabel(p.span_cols || 3, p.span_rows || 2),
            photo: p
        }));

        // Détection des trous dans cette ligne pour les proposer comme slots vides
        const gridOccupied = {};
        slots.forEach(s => {
            for (let r = s.row_start; r < s.row_start + s.span_rows; r++) {
                for (let c = s.col_start; c < s.col_start + s.span_cols; c++) {
                    gridOccupied[`${r},${c}`] = true;
                }
            }
        });

        let gapCount = 1;
        for (let r = 1; r <= totalRows; r++) {
            for (let c = 1; c <= 12; c++) {
                if (!gridOccupied[`${r},${c}`]) {
                    let w = 0;
                    while (c + w <= 12 && !gridOccupied[`${r},${c + w}`]) {
                        w++;
                    }
                    let h = 1;
                    let canExpand = true;
                    while (canExpand && (r + h <= totalRows)) {
                        for (let tc = c; tc < c + w; tc++) {
                            if (gridOccupied[`${r + h},${tc}`]) {
                                canExpand = false;
                                break;
                            }
                        }
                        if (canExpand) h++;
                    }
                    for (let mr = r; mr < r + h; mr++) {
                        for (let mc = c; mc < c + w; mc++) {
                            gridOccupied[`${mr},${mc}`] = true;
                        }
                    }

                    let lbl = 'Petit Paysage';
                    if (w === 6 && h === 4) lbl = 'Grand Paysage';
                    else if (w === 4 && h === 6) lbl = 'Grand Portrait';
                    else if (w === 2 && h === 3) lbl = 'Petit Portrait';

                    slots.push({
                        id: `slot-empty-${bIdx}-${gapCount++}`,
                        col_start: c,
                        row_start: r,
                        span_cols: w,
                        span_rows: h,
                        label: lbl,
                        photo: null
                    });
                }
            }
        }

        slots.sort((a, b) => {
            if (a.row_start !== b.row_start) return a.row_start - b.row_start;
            return a.col_start - b.col_start;
        });

        return {
            id: block.id,
            totalRows: totalRows,
            slots: slots
        };
    });
};

const getSlotGridStyle = (slot) => {
    return {
        gridColumn: `${slot.col_start} / span ${slot.span_cols}`,
        gridRow: `${slot.row_start} / span ${slot.span_rows}`,
    };
};

// Ouvrir la modale spacieuse d'ajout de ligne
const openAddRowModal = (targetIndex = null) => {
    addRowTargetIndex.value = targetIndex;
    showAddRowModal.value = true;
};

// Insérer une nouvelle ligne harmonique VIDE avec ses slots prévus
const applyHarmonyTemplate = (template, insertIndex = null) => {
    const newRow = {
        id: 'row-' + Date.now() + '-' + Math.random().toString(36).substr(2, 5),
        totalRows: template.totalRows,
        slots: template.slots.map((s, idx) => ({
            id: `slot-${Date.now()}-${idx}`,
            col_start: s.col_start,
            row_start: s.row_start,
            span_cols: s.span_cols,
            span_rows: s.span_rows,
            label: s.label || getFormatLabel(s.span_cols, s.span_rows),
            photo: null, // TOUJOURS VIDE PAR DÉFAUT !
        }))
    };

    if (insertIndex !== null && insertIndex !== undefined && insertIndex >= 0 && insertIndex <= compositionRows.value.length) {
        compositionRows.value.splice(insertIndex, 0, newRow);
    } else {
        compositionRows.value.push(newRow);
    }

    showAddRowModal.value = false;
    syncAllPositionsAndSave();
};

// Drag & drop des photos
const onPhotoDragStart = (e, slot, rIdx) => {
    e.dataTransfer.effectAllowed = 'move';
    draggedItem.value = {
        photo: slot.photo,
        fromSlot: slot,
        fromRowIndex: rIdx,
        fromDock: false,
    };
    e.dataTransfer.setData('text/plain', slot.photo.id);
};

const onDockPhotoDragStart = (e, photo) => {
    e.dataTransfer.effectAllowed = 'move';
    draggedItem.value = {
        photo: photo,
        fromSlot: null,
        fromRowIndex: null,
        fromDock: true,
    };
    e.dataTransfer.setData('text/plain', photo.id);
};

const onSlotDragEnter = (e, slot) => {
    dragOverSlot.value = slot;
};

const onSlotDragOver = (e, slot) => {
    e.preventDefault();
    dragOverSlot.value = slot;
    e.dataTransfer.dropEffect = 'move';
};

const onDropOnSlot = (e, targetSlot, targetRowIndex) => {
    e.preventDefault();
    if (!draggedItem.value || !draggedItem.value.photo) {
        onPhotoDragEnd();
        return;
    }

    const draggedPhoto = draggedItem.value.photo;
    const fromSlot = draggedItem.value.fromSlot;

    if (fromSlot && fromSlot.id === targetSlot.id) {
        onPhotoDragEnd();
        return;
    }

    if (!targetSlot.photo) {
        // Slot cible est VIDE
        if (fromSlot) {
            fromSlot.photo = null;
        }
        targetSlot.photo = draggedPhoto;
        draggedPhoto.span_cols = targetSlot.span_cols;
        draggedPhoto.span_rows = targetSlot.span_rows;
    } else {
        // Slot cible est OCCUPÉ
        const targetPhoto = targetSlot.photo;
        if (fromSlot) {
            // SWAP
            targetSlot.photo = draggedPhoto;
            draggedPhoto.span_cols = targetSlot.span_cols;
            draggedPhoto.span_rows = targetSlot.span_rows;

            fromSlot.photo = targetPhoto;
            targetPhoto.span_cols = fromSlot.span_cols;
            targetPhoto.span_rows = fromSlot.span_rows;
        } else {
            // Vient du dock : remplace la photo du slot, l'ancienne retourne au dock
            targetPhoto.col_start = null;
            targetPhoto.row_start = null;
            targetSlot.photo = draggedPhoto;
            draggedPhoto.span_cols = targetSlot.span_cols;
            draggedPhoto.span_rows = targetSlot.span_rows;
        }
    }

    syncAllPositionsAndSave();
    onPhotoDragEnd();
};

const onRowDropPhoto = (e, targetRowIndex) => {
    e.preventDefault();
    if (!draggedItem.value || !draggedItem.value.photo) return;
    const targetRow = compositionRows.value[targetRowIndex];
    if (!targetRow) return;

    const emptySlot = targetRow.slots.find(s => !s.photo);
    if (emptySlot) {
        onDropOnSlot(e, emptySlot, targetRowIndex);
    } else if (targetRow.slots.length > 0) {
        onDropOnSlot(e, targetRow.slots[0], targetRowIndex);
    }
};

const detachPhotoFromSlot = (slot, rIdx) => {
    if (!slot.photo) return;
    slot.photo.col_start = null;
    slot.photo.row_start = null;
    slot.photo = null;
    syncAllPositionsAndSave();
};

const onDropOnDock = (e) => {
    e.preventDefault();
    if (!draggedItem.value || !draggedItem.value.photo) return;
    if (draggedItem.value.fromSlot) {
        detachPhotoFromSlot(draggedItem.value.fromSlot, draggedItem.value.fromRowIndex);
    }
    onPhotoDragEnd();
};

const onPhotoDragEnd = () => {
    draggedItem.value = null;
    dragOverSlot.value = null;
};

// Remplissage automatique des slots vides avec les photos en attente
const autoFillEmptySlots = () => {
    const available = [...unplacedPhotos.value];
    if (available.length === 0) return;

    compositionRows.value.forEach(row => {
        row.slots.forEach(slot => {
            if (!slot.photo && available.length > 0) {
                const isLandscapeSlot = slot.span_cols >= slot.span_rows;
                const matchIdx = available.findIndex(p => {
                    const isLand = (p.span_cols || 3) >= (p.span_rows || 2);
                    return isLandscapeSlot === isLand;
                });
                const chosen = matchIdx !== -1 ? available.splice(matchIdx, 1)[0] : available.shift();
                slot.photo = chosen;
                chosen.span_cols = slot.span_cols;
                chosen.span_rows = slot.span_rows;
            }
        });
    });

    syncAllPositionsAndSave();
};

// Inversion (effet miroir gauche/droite) d'une ligne
const invertRowBlock = (row) => {
    if (!row || !row.slots) return;
    row.slots.forEach(slot => {
        const w = slot.span_cols || 3;
        const oldCol = slot.col_start || 1;
        slot.col_start = Math.max(1, Math.min(12 - w + 1, 13 - (oldCol + w - 1)));
    });
    row.slots.sort((a, b) => {
        if (a.row_start !== b.row_start) return a.row_start - b.row_start;
        return a.col_start - b.col_start;
    });
    syncAllPositionsAndSave();
};

// Déplacement de ligne entière vers le haut / bas
const moveRowBlockUp = (rIdx) => {
    if (rIdx <= 0) return;
    const row = compositionRows.value.splice(rIdx, 1)[0];
    compositionRows.value.splice(rIdx - 1, 0, row);
    syncAllPositionsAndSave();
};

const moveRowBlockDown = (rIdx) => {
    if (rIdx >= compositionRows.value.length - 1) return;
    const row = compositionRows.value.splice(rIdx, 1)[0];
    compositionRows.value.splice(rIdx + 1, 0, row);
    syncAllPositionsAndSave();
};

// Suppression d'une ligne harmonique
const removeHarmonicRow = (rIdx) => {
    const row = compositionRows.value[rIdx];
    if (!row) return;
    row.slots.forEach(slot => {
        if (slot.photo) {
            slot.photo.col_start = null;
            slot.photo.row_start = null;
        }
    });
    compositionRows.value.splice(rIdx, 1);
    syncAllPositionsAndSave();
};

// Drag & drop de lignes entières
const onRowDragStart = (e, index) => {
    draggingRowIndex.value = index;
    dragOverRowIndex.value = index;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', `row-${index}`);
};

const onRowDragOver = (e, index) => {
    e.preventDefault();
    dragOverRowIndex.value = index;
    e.dataTransfer.dropEffect = 'move';
};

const onRowDrop = (e, targetIndex) => {
    e.preventDefault();
    if (draggingRowIndex.value === null || draggingRowIndex.value === targetIndex) {
        draggingRowIndex.value = null;
        dragOverRowIndex.value = null;
        return;
    }
    const row = compositionRows.value.splice(draggingRowIndex.value, 1)[0];
    compositionRows.value.splice(targetIndex, 0, row);
    draggingRowIndex.value = null;
    dragOverRowIndex.value = null;
    syncAllPositionsAndSave();
};

// --- DÉPLACEMENT DE SLOTS (GAUCHE / DROITE / HAUT / BAS) & DIVISION / FUSION ---

// Vérifie si la boîte rectangulaire [c1..c2] x [r1..r2] est fermée (aucun slot ne déborde et aire pleine)
const isBoxClosed = (slots, c1, c2, r1, r2) => {
    if (c1 > c2 || r1 > r2) return false;
    let area = 0;
    for (const s of slots) {
        const sc1 = s.col_start;
        const sc2 = s.col_start + s.span_cols - 1;
        const sr1 = s.row_start;
        const sr2 = s.row_start + s.span_rows - 1;
        const overlapsC = !(sc2 < c1 || sc1 > c2);
        const overlapsR = !(sr2 < r1 || sr1 > r2);
        if (overlapsC && overlapsR) {
            if (sc1 < c1 || sc2 > c2 || sr1 < r1 || sr2 > r2) {
                return false;
            }
            area += s.span_cols * s.span_rows;
        }
    }
    return area === (c2 - c1 + 1) * (r2 - r1 + 1);
};

const getSlotsInBox = (slots, c1, c2, r1, r2) => {
    return slots.filter(s =>
        s.col_start >= c1 && (s.col_start + s.span_cols - 1) <= c2 &&
        s.row_start >= r1 && (s.row_start + s.span_rows - 1) <= r2
    );
};

// Évite les échanges visuellement identiques quand tous les slots sont vides (ex: échanger 2 slots vides 3x2)
const isSwapNoOp = (slotsB, slotsBPrime, dcB, drB, dcBPrime, drBPrime) => {
    const anyPhoto = slotsB.some(s => !!s.photo) || slotsBPrime.some(s => !!s.photo);
    if (anyPhoto) return false;
    const beforeSet = new Set([
        ...slotsB.map(s => `${s.col_start},${s.row_start},${s.span_cols},${s.span_rows}`),
        ...slotsBPrime.map(s => `${s.col_start},${s.row_start},${s.span_cols},${s.span_rows}`),
    ]);
    const afterSet = new Set([
        ...slotsB.map(s => `${s.col_start + dcB},${s.row_start + drB},${s.span_cols},${s.span_rows}`),
        ...slotsBPrime.map(s => `${s.col_start + dcBPrime},${s.row_start + drBPrime},${s.span_cols},${s.span_rows}`),
    ]);
    if (beforeSet.size !== afterSet.size) return false;
    for (const k of beforeSet) {
        if (!afterSet.has(k)) return false;
    }
    return true;
};

// Cherche le déplacement local optimal d'un slot (ou de son sous-bloc harmonique) dans une direction
const findSlotMove = (row, slot, dir) => {
    if (!row || !Array.isArray(row.slots) || !slot) return null;
    const slots = row.slots;
    const H = row.totalRows || Math.max(...slots.map(s => s.row_start + s.span_rows - 1), 2);
    const sc1 = slot.col_start;
    const sc2 = slot.col_start + slot.span_cols - 1;
    const sr1 = slot.row_start;
    const sr2 = slot.row_start + slot.span_rows - 1;

    const candidates = [];

    if (dir === 'left' || dir === 'right') {
        for (let r1 = 1; r1 <= sr1; r1++) {
            for (let r2 = sr2; r2 <= H; r2++) {
                for (let c1 = 1; c1 <= sc1; c1++) {
                    for (let c2 = sc2; c2 <= 12; c2++) {
                        if (!isBoxClosed(slots, c1, c2, r1, r2)) continue;
                        const slotsB = getSlotsInBox(slots, c1, c2, r1, r2);
                        const wB = c2 - c1 + 1;
                        const hSpan = r2 - r1 + 1;

                        if (dir === 'left' && c1 > 1) {
                            for (let c0 = c1 - 1; c0 >= 1; c0--) {
                                if (isBoxClosed(slots, c0, c1 - 1, r1, r2)) {
                                    const slotsBPrime = getSlotsInBox(slots, c0, c1 - 1, r1, r2);
                                    const wBPrime = c1 - c0;
                                    if (!isSwapNoOp(slotsB, slotsBPrime, -wBPrime, 0, wB, 0)) {
                                        candidates.push({
                                            slotsB, slotsBPrime,
                                            dcB: -wBPrime, drB: 0,
                                            dcBPrime: wB, drBPrime: 0,
                                            score: hSpan * 1000 + wB * 50 + wBPrime
                                        });
                                    }
                                    break;
                                }
                            }
                        } else if (dir === 'right' && c2 < 12) {
                            for (let c3 = c2 + 1; c3 <= 12; c3++) {
                                if (isBoxClosed(slots, c2 + 1, c3, r1, r2)) {
                                    const slotsBPrime = getSlotsInBox(slots, c2 + 1, c3, r1, r2);
                                    const wBPrime = c3 - c2;
                                    if (!isSwapNoOp(slotsB, slotsBPrime, wBPrime, 0, -wB, 0)) {
                                        candidates.push({
                                            slotsB, slotsBPrime,
                                            dcB: wBPrime, drB: 0,
                                            dcBPrime: -wB, drBPrime: 0,
                                            score: hSpan * 1000 + wB * 50 + wBPrime
                                        });
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    } else if (dir === 'up' || dir === 'down') {
        for (let c1 = 1; c1 <= sc1; c1++) {
            for (let c2 = sc2; c2 <= 12; c2++) {
                for (let r1 = 1; r1 <= sr1; r1++) {
                    for (let r2 = sr2; r2 <= H; r2++) {
                        if (!isBoxClosed(slots, c1, c2, r1, r2)) continue;
                        const slotsB = getSlotsInBox(slots, c1, c2, r1, r2);
                        const hB = r2 - r1 + 1;
                        const wSpan = c2 - c1 + 1;

                        if (dir === 'up' && r1 > 1) {
                            for (let r0 = r1 - 1; r0 >= 1; r0--) {
                                if (isBoxClosed(slots, c1, c2, r0, r1 - 1)) {
                                    const slotsBPrime = getSlotsInBox(slots, c1, c2, r0, r1 - 1);
                                    const hBPrime = r1 - r0;
                                    if (!isSwapNoOp(slotsB, slotsBPrime, 0, -hBPrime, 0, hB)) {
                                        candidates.push({
                                            slotsB, slotsBPrime,
                                            dcB: 0, drB: -hBPrime,
                                            dcBPrime: 0, drBPrime: hB,
                                            score: wSpan * 1000 + hB * 50 + hBPrime
                                        });
                                    }
                                    break;
                                }
                            }
                        } else if (dir === 'down' && r2 < H) {
                            for (let r3 = r2 + 1; r3 <= H; r3++) {
                                if (isBoxClosed(slots, c1, c2, r2 + 1, r3)) {
                                    const slotsBPrime = getSlotsInBox(slots, c1, c2, r2 + 1, r3);
                                    const hBPrime = r3 - r2;
                                    if (!isSwapNoOp(slotsB, slotsBPrime, 0, hBPrime, 0, -hB)) {
                                        candidates.push({
                                            slotsB, slotsBPrime,
                                            dcB: 0, drB: hBPrime,
                                            dcBPrime: 0, drBPrime: -hB,
                                            score: wSpan * 1000 + hB * 50 + hBPrime
                                        });
                                    }
                                    break;
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    if (candidates.length === 0) return null;
    candidates.sort((a, b) => a.score - b.score);
    return candidates[0];
};

const moveSlotInRow = (row, slot, dir) => {
    const move = findSlotMove(row, slot, dir);
    if (!move) return;
    move.slotsB.forEach(s => {
        s.col_start += move.dcB;
        s.row_start += move.drB;
    });
    move.slotsBPrime.forEach(s => {
        s.col_start += move.dcBPrime;
        s.row_start += move.drBPrime;
    });
    row.slots.sort((a, b) => (a.row_start - b.row_start) || (a.col_start - b.col_start));
    syncAllPositionsAndSave();
};

// Division d'un Grand format (6x4 ou 4x6) en 4 Petits formats (3x2 ou 2x3)
const canSubdivideSlot = (slot) => {
    if (!slot) return false;
    return (slot.span_cols === 6 && slot.span_rows === 4) || (slot.span_cols === 4 && slot.span_rows === 6);
};

const subdivideSlot = (row, slot) => {
    if (!row || !Array.isArray(row.slots) || !canSubdivideSlot(slot)) return;
    const isLandscape = slot.span_cols === 6 && slot.span_rows === 4;
    const subW = isLandscape ? 3 : 2;
    const subH = isLandscape ? 2 : 3;
    const subLabel = isLandscape ? 'Petit Paysage' : 'Petit Portrait';
    const c = slot.col_start;
    const r = slot.row_start;
    const existingPhoto = slot.photo || null;

    if (existingPhoto) {
        existingPhoto.span_cols = subW;
        existingPhoto.span_rows = subH;
    }

    const idx = row.slots.findIndex(s => s.id === slot.id);
    if (idx === -1) return;

    const baseId = `slot-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`;
    const newSlots = [
        { id: `${baseId}-0`, col_start: c,        row_start: r,        span_cols: subW, span_rows: subH, label: subLabel, photo: existingPhoto },
        { id: `${baseId}-1`, col_start: c + subW, row_start: r,        span_cols: subW, span_rows: subH, label: subLabel, photo: null },
        { id: `${baseId}-2`, col_start: c,        row_start: r + subH, span_cols: subW, span_rows: subH, label: subLabel, photo: null },
        { id: `${baseId}-3`, col_start: c + subW, row_start: r + subH, span_cols: subW, span_rows: subH, label: subLabel, photo: null },
    ];

    row.slots.splice(idx, 1, ...newSlots);
    row.slots.sort((a, b) => (a.row_start - b.row_start) || (a.col_start - b.col_start));
    syncAllPositionsAndSave();
};

// Fusion de 4 Petits formats (2x2 de 3x2 ou 2x3) en 1 Grand format (6x4 ou 4x6)
const findMergeGroup = (row, slot) => {
    if (!row || !Array.isArray(row.slots) || !slot) return null;
    const isSmallLandscape = slot.span_cols === 3 && slot.span_rows === 2;
    const isSmallPortrait = slot.span_cols === 2 && slot.span_rows === 3;
    if (!isSmallLandscape && !isSmallPortrait) return null;

    const subW = slot.span_cols;
    const subH = slot.span_rows;
    const bigW = subW * 2;
    const bigH = subH * 2;
    const bigLabel = isSmallLandscape ? 'Grand Paysage' : 'Grand Portrait';

    const possibleAnchors = [
        [slot.col_start - subW, slot.row_start - subH],
        [slot.col_start,        slot.row_start - subH],
        [slot.col_start - subW, slot.row_start],
        [slot.col_start,        slot.row_start],
    ];

    possibleAnchors.sort((a, b) => {
        const aAligned = ((a[0] - 1) % bigW === 0 ? 0 : 1) + ((a[1] - 1) % bigH === 0 ? 0 : 1);
        const bAligned = ((b[0] - 1) % bigW === 0 ? 0 : 1) + ((b[1] - 1) % bigH === 0 ? 0 : 1);
        return aAligned - bAligned;
    });

    for (const [ac, ar] of possibleAnchors) {
        if (ac < 1 || ar < 1 || ac + bigW - 1 > 12) continue;
        const s00 = row.slots.find(s => s.col_start === ac && s.row_start === ar && s.span_cols === subW && s.span_rows === subH);
        const s10 = row.slots.find(s => s.col_start === ac + subW && s.row_start === ar && s.span_cols === subW && s.span_rows === subH);
        const s01 = row.slots.find(s => s.col_start === ac && s.row_start === ar + subH && s.span_cols === subW && s.span_rows === subH);
        const s11 = row.slots.find(s => s.col_start === ac + subW && s.row_start === ar + subH && s.span_cols === subW && s.span_rows === subH);
        if (s00 && s10 && s01 && s11) {
            return {
                anchorC: ac,
                anchorR: ar,
                bigW,
                bigH,
                bigLabel,
                group: [s00, s10, s01, s11],
            };
        }
    }
    return null;
};

const mergeSlots = (row, slot) => {
    const mergeInfo = findMergeGroup(row, slot);
    if (!mergeInfo) return;

    const { anchorC, anchorR, bigW, bigH, bigLabel, group } = mergeInfo;
    const photosInGroup = group.map(s => s.photo).filter(Boolean);
    const keptPhoto = photosInGroup[0] || null;

    // Remettre les éventuelles photos supplémentaires en attente dans le dock
    for (let i = 1; i < photosInGroup.length; i++) {
        photosInGroup[i].col_start = null;
        photosInGroup[i].row_start = null;
    }

    if (keptPhoto) {
        keptPhoto.span_cols = bigW;
        keptPhoto.span_rows = bigH;
    }

    const groupIds = new Set(group.map(s => s.id));
    row.slots = row.slots.filter(s => !groupIds.has(s.id));
    row.slots.push({
        id: `slot-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
        col_start: anchorC,
        row_start: anchorR,
        span_cols: bigW,
        span_rows: bigH,
        label: bigLabel,
        photo: keptPhoto,
    });
    row.slots.sort((a, b) => (a.row_start - b.row_start) || (a.col_start - b.col_start));
    syncAllPositionsAndSave();
};


// Calcul et sauvegarde de toutes les positions globales 2D
const syncAllPositionsAndSave = () => {
    let currentRowStart = 1;
    const finalPhotos = [];
    const placedPhotoIds = new Set();

    compositionRows.value.forEach(row => {
        let maxRowInBlock = 0;
        row.slots.forEach(slot => {
            const endR = slot.row_start + slot.span_rows - 1;
            if (endR > maxRowInBlock) maxRowInBlock = endR;

            if (slot.photo) {
                slot.photo.col_start = slot.col_start;
                slot.photo.row_start = currentRowStart + slot.row_start - 1;
                slot.photo.span_cols = slot.span_cols;
                slot.photo.span_rows = slot.span_rows;
                finalPhotos.push(slot.photo);
                placedPhotoIds.add(slot.photo.id);
            }
        });
        const blockHeight = row.totalRows || maxRowInBlock || 2;
        currentRowStart += blockHeight;
    });

    // Photos non placées
    props.photos.forEach(p => {
        if (!placedPhotoIds.has(p.id)) {
            p.col_start = null;
            p.row_start = null;
            finalPhotos.push(p);
        }
    });

    finalPhotos.forEach((p, idx) => p.display_order = idx);
    props.photos.splice(0, props.photos.length, ...finalPhotos);

    saveOrderToServer();
};

const saveOrderToServer = () => {
    const order = props.photos.map((p, i) => ({
        id: p.id,
        display_order: i,
        col_start: p.col_start,
        row_start: p.row_start,
        span_cols: p.span_cols,
        span_rows: p.span_rows,
    }));

    router.post('/admin/photos/reorder', { order }, {
        preserveScroll: true,
        onSuccess: () => {
            nextTick(updateAdminGridUnit);
        }
    });
};

// --- Sélection & Actions groupées ---
const selectedPhotoIds = ref([]);
const bulkTagInput = ref('');
const bulkCategoryId = ref(null);
const showBulkTagModal = ref(false);
const showBulkCategoryModal = ref(false);
const confirmBulkDelete = ref(false);

const isAllSelected = () => {
    return props.photos.length > 0 && selectedPhotoIds.value.length === props.photos.length;
};

const toggleSelectAll = () => {
    if (isAllSelected()) {
        selectedPhotoIds.value = [];
    } else {
        selectedPhotoIds.value = props.photos.map(p => p.id);
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

    props.photos.forEach(p => {
        if (selectedPhotoIds.value.includes(p.id)) {
            p.span_cols = fmt.cols;
            p.span_rows = fmt.rows;
        }
    });

    router.post('/admin/photos/bulk-action', {
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

    props.photos.forEach(p => {
        if (selectedPhotoIds.value.includes(p.id)) {
            p.is_highlighted = status;
        }
    });

    router.post('/admin/photos/bulk-action', {
        ids: selectedPhotoIds.value,
        action: status ? 'set_highlight' : 'remove_highlight',
    }, { preserveScroll: true });
};

const applyBulkCategory = () => {
    if (selectedPhotoIds.value.length === 0) return;

    const chosenCat = props.categories.find(c => c.id === bulkCategoryId.value);
    props.photos.forEach(p => {
        if (selectedPhotoIds.value.includes(p.id)) {
            p.category_id = bulkCategoryId.value;
            p.category = chosenCat || null;
        }
    });

    router.post('/admin/photos/bulk-action', {
        ids: selectedPhotoIds.value,
        action: 'set_category',
        category_id: bulkCategoryId.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkCategoryModal.value = false;
        }
    });
};

const applyBulkInstagramTag = () => {
    if (selectedPhotoIds.value.length === 0) return;

    props.photos.forEach(p => {
        if (selectedPhotoIds.value.includes(p.id)) {
            p.instagram_tag = bulkTagInput.value;
        }
    });

    router.post('/admin/photos/bulk-action', {
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
    const deletedSet = new Set(selectedPhotoIds.value);

    router.post('/admin/photos/bulk-destroy', {
        ids: selectedPhotoIds.value,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            compositionRows.value.forEach(r => {
                r.slots.forEach(s => {
                    if (s.photo && deletedSet.has(s.photo.id)) s.photo = null;
                });
            });
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
    initCompositionRows();
    updateAdminGridUnit();
    if (window.ResizeObserver && adminGridRef.value) {
        resizeObserver = new ResizeObserver(() => updateAdminGridUnit());
        resizeObserver.observe(adminGridRef.value);
    }
});

watch(() => props.photos, (newPhotos) => {
    if (compositionRows.value.length === 0 && newPhotos && newPhotos.length > 0) {
        initCompositionRows();
    }
}, { deep: true });

onUnmounted(() => {
    if (resizeObserver) resizeObserver.disconnect();
});
</script>

<template>
    <Head title="Photos en vrac" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex-between">
                <div>
                    <h2>Photos en vrac</h2>
                    <div style="font-size: 0.85rem; color: var(--text-muted);">Gestion de la galerie autonome sans projet lié</div>
                </div>
                <div style="display: flex; gap: var(--space-sm);">
                    <a href="/galerie" target="_blank" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 6px;">
                        <PhEye :size="15" weight="bold" />
                        <span>Voir la galerie</span>
                    </a>
                </div>
            </div>
        </template>

        <!-- Upload Card -->
        <div class="card" style="margin-bottom: var(--space-xl);">
            <div style="display: grid; grid-template-columns: 1fr 300px; gap: var(--space-xl); align-items: start;">
                <div>
                    <h3 style="margin-bottom: var(--space-sm);">Importer des photos</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: var(--space-md);">
                        Ces photos apparaîtront dans la section "Galerie" du site. L'orientation (Paysage / Portrait) est détectée automatiquement.
                    </p>
                    
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
                                <span style="display: inline-flex; align-items: center; gap: 6px;">
                                    <PhCamera :size="15" weight="duotone" />
                                    <span><strong>{{ uploadProgress.uploadedCount }}</strong> / {{ uploadProgress.totalCount }} photos</span>
                                </span>
                                <span v-if="uploadProgress.totalBatches > 1" class="batch-badge">
                                    Lot {{ uploadProgress.currentBatch }} / {{ uploadProgress.totalBatches }}
                                </span>
                                <span class="batch-percent">{{ uploadProgress.percent }}%</span>
                            </div>
                        </div>

                        <!-- Idle state -->
                        <div v-else>
                            <div style="display: flex; justify-content: center; margin-bottom: var(--space-sm);">
                                <PhUploadSimple :size="36" weight="duotone" style="color: var(--primary-light);" />
                            </div>
                            <div style="color: var(--text-secondary); font-weight: 500;">
                                {{ isDragOver ? 'Relâchez pour uploader !' : 'Cliquez ou glissez vos photos ici' }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: var(--space-xs);">
                                Découpage automatique en lots fluides (60+, 100+...) — Orientation auto & WebP
                            </div>
                        </div>
                    </label>
                </div>

                <div class="card" style="background: var(--bg-elevated);">
                    <h4 style="margin-bottom: var(--space-md); color: var(--text-secondary);">Catégorie par défaut</h4>
                    <div class="form-group">
                        <PremiumSelect 
                            v-model="uploadForm.category_id"
                            :options="categories"
                            placeholder="Choisir une catégorie"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Header & View Mode Switcher -->
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
                            Galerie ({{ photos.length }} photos)
                        </h3>
                        <span v-if="selectedPhotoIds.length > 0" class="selection-counter-badge">
                            {{ selectedPhotoIds.length }} sélectionnée{{ selectedPhotoIds.length > 1 ? 's' : '' }}
                        </span>
                    </div>
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

                        <!-- Catégorie groupée -->
                        <button type="button" class="bulk-btn" @click="showBulkCategoryModal = true" style="display: inline-flex; align-items: center; gap: 6px;">
                            <PhTag :size="15" weight="duotone" />
                            <span>Catégorie</span>
                        </button>

                        <div class="bulk-divider"></div>

                        <!-- Coups de cœur -->
                        <div class="bulk-group">
                            <button type="button" class="bulk-btn" @click="applyBulkHighlight(true)" title="Ajouter aux coups de cœur d'accueil">
                                ★ Coup de cœur
                            </button>
                            <button type="button" class="bulk-btn" @click="applyBulkHighlight(false)" title="Retirer des coups de cœur">
                                ☆ Retirer
                            </button>
                        </div>

                        <div class="bulk-divider"></div>

                        <!-- Tag Instagram -->
                        <button type="button" class="bulk-btn" @click="showBulkTagModal = true" style="display: inline-flex; align-items: center; gap: 6px;">
                            <PhInstagramLogo :size="15" weight="fill" />
                            <span>Tag Insta</span>
                        </button>

                        <div class="bulk-divider"></div>

                        <!-- Supprimer -->
                        <button type="button" class="bulk-btn bulk-btn-danger" @click="confirmBulkDelete = true" style="display: inline-flex; align-items: center; gap: 6px;">
                            <PhTrash :size="15" weight="bold" />
                            <span>Supprimer ({{ selectedPhotoIds.length }})</span>
                        </button>

                        <button type="button" class="bulk-btn-close" @click="selectedPhotoIds = []" title="Désélectionner tout">
                            ✕
                        </button>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- 1. VUE MOSAÏQUE RÉELLE (WYSIWYG INTERACTIVE PAR SLOTS) -->
        <div v-if="photos.length > 0">
            <!-- Bac de photos en attente de placement (Dock) -->
            <div 
                v-if="unplacedPhotos.length > 0" 
                class="unplaced-photos-dock"
                @dragover.prevent
                @drop.prevent="onDropOnDock"
            >
                <div class="dock-header">
                    <div class="dock-title">
                        <PhImages :size="18" weight="bold" />
                        <span>Photos en attente de placement ({{ unplacedPhotos.length }})</span>
                    </div>
                    <div class="dock-actions">
                        <span class="dock-hint">Glissez les photos dans les emplacements vides ci-dessous</span>
                        <button type="button" class="dock-btn-auto" @click="autoFillEmptySlots">
                            ⚡ Remplir automatiquement les emplacements vides
                        </button>
                    </div>
                </div>
                <div class="dock-photos-strip">
                    <div 
                        v-for="photo in unplacedPhotos"
                        :key="photo.id"
                        class="dock-photo-item"
                        draggable="true"
                        @dragstart="onDockPhotoDragStart($event, photo)"
                        @click="openPhotoEdit(photo)"
                        :title="`Glisser cette photo vers un emplacement vide\n${photo.caption || 'Sans légende'}`"
                    >
                        <img :src="`/storage/${photo.thumbnail_path || photo.image_path}`" loading="lazy" />
                        <span class="dock-photo-badge">
                            {{ ((photo.span_cols || 3) >= (photo.span_rows || 2)) ? 'Paysage' : 'Portrait' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="admin-mosaic-grid" ref="adminGridRef">
                <!-- Message si aucune ligne harmonique n'a encore été créée -->
                <div v-if="compositionRows.length === 0" class="empty-rows-banner">
                    <div class="empty-rows-title">Votre galerie n'a pas encore de ligne harmonique</div>
                    <p class="empty-rows-text">
                        Choisissez une première structure géométrique pour y glisser vos photos sans aucun espace vide.
                    </p>
                    <button
                        type="button"
                        class="btn-add-harmonic-row"
                        style="margin-top: var(--space-md);"
                        @click="openAddRowModal(0)"
                    >
                        ➕ Choisir une première ligne harmonique
                    </button>
                </div>

                <!-- Blocs de lignes harmoniques étagées -->
                <div
                    v-for="(row, bIdx) in compositionRows"
                    :key="row.id"
                    class="admin-composition-row-block"
                    :class="{
                        'is-row-dragging': draggingRowIndex === bIdx,
                        'is-row-drag-over': dragOverRowIndex === bIdx,
                    }"
                    @dragover.prevent="onRowDragOver($event, bIdx)"
                    @drop.prevent="onRowDrop($event, bIdx)"
                >
                    <!-- Barre d'en-tête de ligne (permet aussi de balancer une photo directement sur la ligne) -->
                    <div 
                        class="admin-mosaic-row-bar"
                        @dragover.prevent
                        @drop.prevent.stop="onRowDropPhoto($event, bIdx)"
                    >
                        <div class="row-bar-info">
                            <div 
                                class="row-drag-handle"
                                draggable="true"
                                @dragstart.stop="onRowDragStart($event, bIdx)"
                                title="Glisser pour déplacer cette ligne complète"
                            >
                                ⠿
                            </div>
                            <span class="row-bar-tag">Ligne #{{ bIdx + 1 }}</span>
                            <span class="row-bar-count">
                                {{ row.slots.filter(s => s.photo).length }} / {{ row.slots.length }} photo{{ row.slots.length > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="row-bar-actions">
                            <button
                                type="button"
                                class="row-bar-btn"
                                :disabled="bIdx === 0"
                                @click.stop="moveRowBlockUp(bIdx)"
                                title="Monter cette ligne complète"
                            >
                                ▲ Monter
                            </button>
                            <button
                                type="button"
                                class="row-bar-btn"
                                :disabled="bIdx === compositionRows.length - 1"
                                @click.stop="moveRowBlockDown(bIdx)"
                                title="Descendre cette ligne complète"
                            >
                                ▼ Descendre
                            </button>
                            <button
                                type="button"
                                class="row-bar-btn"
                                @click.stop="invertRowBlock(row)"
                                title="Inverser gauche/droite la disposition des photos de cette ligne"
                            >
                                ⇄ Inverser
                            </button>
                            <button
                                type="button"
                                class="row-bar-btn row-bar-btn-add"
                                @click.stop="openAddRowModal(bIdx + 1)"
                                title="Insérer une nouvelle ligne harmonique en-dessous"
                            >
                                + Insérer ligne
                            </button>
                            <button
                                type="button"
                                class="row-bar-btn row-bar-btn-danger"
                                @click.stop="removeHarmonicRow(bIdx)"
                                title="Supprimer cette ligne (les photos retournent en attente)"
                            >
                                ✕
                            </button>
                        </div>
                    </div>

                    <!-- Grille 12 colonnes de cette ligne -->
                    <div 
                        class="admin-mosaic-row-grid"
                        :style="{
                            gridAutoRows: 'var(--grid-unit, 70px)',
                        }"
                    >
                        <template v-for="(slot, sIdx) in row.slots" :key="slot.id || sIdx">
                            <!-- 1. SLOT OCCUPÉ PAR UNE PHOTO -->
                            <div
                                v-if="slot.photo"
                                class="admin-mosaic-item"
                                :class="[
                                    `span-c-${slot.photo.span_cols || slot.span_cols}`,
                                    `span-r-${slot.photo.span_rows || slot.span_rows}`,
                                    {
                                        'is-dragging': draggedItem && draggedItem.photo && draggedItem.photo.id === slot.photo.id,
                                        'is-drag-over': dragOverSlot && dragOverSlot.id === slot.id,
                                        'is-highlighted': slot.photo.is_highlighted,
                                        'is-selected': isPhotoSelected(slot.photo.id)
                                    }
                                ]"
                                :style="getSlotGridStyle(slot)"
                                draggable="true"
                                @dragstart="onPhotoDragStart($event, slot, bIdx)"
                                @dragenter.prevent="onSlotDragEnter($event, slot)"
                                @dragover.prevent="onSlotDragOver($event, slot)"
                                @dragend="onPhotoDragEnd"
                                @drop.prevent="onDropOnSlot($event, slot, bIdx)"
                            >
                                <div class="admin-mosaic-img-wrap">
                                    <img
                                        :src="`/storage/${slot.photo.thumbnail_path || slot.photo.image_path}`"
                                        :alt="slot.photo.caption || 'Photo'"
                                        loading="lazy"
                                    />
                                </div>

                                <div class="admin-mosaic-topbar">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <label class="item-checkbox-wrap" @click.stop>
                                            <input 
                                                type="checkbox" 
                                                class="bulk-checkbox"
                                                :checked="isPhotoSelected(slot.photo.id)" 
                                                @change="toggleSelectPhoto(slot.photo.id)" 
                                            />
                                        </label>

                                        <div class="admin-mosaic-badge-order" :title="`Ordre #${(slot.photo.display_order || 0) + 1} · Format: ${slot.photo.span_cols >= slot.photo.span_rows ? 'Paysage' : 'Portrait'}`">
                                            <span class="drag-handle">⠿</span> #{{ (slot.photo.display_order || 0) + 1 }}
                                            <span style="opacity: 0.7; font-size: 0.7rem;">{{ slot.photo.span_cols >= slot.photo.span_rows ? '3:2' : '2:3' }}</span>
                                        </div>
                                    </div>
                                    <div class="admin-mosaic-top-actions">
                                        <span v-if="slot.photo.category" class="badge badge-primary" style="font-size: 0.65rem;">
                                            {{ slot.photo.category.name }}
                                        </span>
                                        <button
                                            type="button"
                                            class="badge-toggle"
                                            :class="{ active: slot.photo.is_highlighted }"
                                            @click.stop="toggleHighlight(slot.photo)"
                                            title="Coup de cœur accueil"
                                        >
                                            {{ slot.photo.is_highlighted ? '★ Star' : '☆' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="btn-icon btn-icon-detach"
                                            @click.stop="detachPhotoFromSlot(slot, bIdx)"
                                            title="Détacher cette photo de la ligne (la remettre en attente)"
                                        >
                                            ↩
                                        </button>
                                    </div>
                                </div>

                                <div class="admin-mosaic-bottombar">
                                    <div class="slot-geometry-actions">
                                        <button
                                            v-if="findSlotMove(row, slot, 'left')"
                                            type="button"
                                            class="slot-geo-btn"
                                            @click.stop="moveSlotInRow(row, slot, 'left')"
                                            title="Décaler vers la gauche"
                                        >←</button>
                                        <button
                                            v-if="findSlotMove(row, slot, 'up')"
                                            type="button"
                                            class="slot-geo-btn"
                                            @click.stop="moveSlotInRow(row, slot, 'up')"
                                            title="Décaler vers le haut"
                                        >↑</button>
                                        <button
                                            v-if="findSlotMove(row, slot, 'down')"
                                            type="button"
                                            class="slot-geo-btn"
                                            @click.stop="moveSlotInRow(row, slot, 'down')"
                                            title="Décaler vers le bas"
                                        >↓</button>
                                        <button
                                            v-if="findSlotMove(row, slot, 'right')"
                                            type="button"
                                            class="slot-geo-btn"
                                            @click.stop="moveSlotInRow(row, slot, 'right')"
                                            title="Décaler vers la droite"
                                        >→</button>
                                        <button
                                            v-if="canSubdivideSlot(slot)"
                                            type="button"
                                            class="slot-geo-btn slot-geo-btn-split"
                                            @click.stop="subdivideSlot(row, slot)"
                                            title="Diviser ce Grand format en 4 Petits formats"
                                        >⊞ ×4</button>
                                        <button
                                            v-if="findMergeGroup(row, slot)"
                                            type="button"
                                            class="slot-geo-btn slot-geo-btn-merge"
                                            @click.stop="mergeSlots(row, slot)"
                                            title="Fusionner les 4 Petits formats en 1 Grand format"
                                        >⬜ Grand</button>
                                    </div>

                                    <div class="admin-card-edit-btns">
                                        <button
                                            type="button"
                                            class="btn-icon"
                                            @click.stop="openPhotoEdit(slot.photo)"
                                            title="Modifier"
                                        >
                                            <PhPencilSimple :size="14" />
                                        </button>
                                        <button
                                            type="button"
                                            class="btn-icon btn-icon-danger"
                                            @click.stop="deletePhoto(slot.photo.id)"
                                            title="Supprimer"
                                        >
                                            <PhTrash :size="14" />
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. SLOT VIDE (Emplacement en attente de photo) -->
                            <div
                                v-else
                                class="admin-mosaic-empty-slot"
                                :class="[
                                    `span-c-${slot.span_cols}`,
                                    `span-r-${slot.span_rows}`,
                                    {
                                        'is-drag-over': dragOverSlot && dragOverSlot.id === slot.id,
                                    }
                                ]"
                                :style="getSlotGridStyle(slot)"
                                @dragenter.prevent="onSlotDragEnter($event, slot)"
                                @dragover.prevent="onSlotDragOver($event, slot)"
                                @drop.prevent="onDropOnSlot($event, slot, bIdx)"
                            >
                                <div class="empty-slot-content">
                                    <span class="empty-slot-icon">{{ (slot.span_cols >= slot.span_rows) ? '🖼️' : '📱' }}</span>
                                    <span class="empty-slot-label">{{ slot.label }}</span>
                                    <span class="empty-slot-sub">Glisser une photo ici</span>

                                    <div class="slot-geometry-actions empty-slot-geo-actions" @click.stop>
                                        <button
                                            v-if="findSlotMove(row, slot, 'left')"
                                            type="button"
                                            class="slot-geo-btn"
                                            @click.stop="moveSlotInRow(row, slot, 'left')"
                                            title="Décaler vers la gauche"
                                        >←</button>
                                        <button
                                            v-if="findSlotMove(row, slot, 'up')"
                                            type="button"
                                            class="slot-geo-btn"
                                            @click.stop="moveSlotInRow(row, slot, 'up')"
                                            title="Décaler vers le haut"
                                        >↑</button>
                                        <button
                                            v-if="findSlotMove(row, slot, 'down')"
                                            type="button"
                                            class="slot-geo-btn"
                                            @click.stop="moveSlotInRow(row, slot, 'down')"
                                            title="Décaler vers le bas"
                                        >↓</button>
                                        <button
                                            v-if="findSlotMove(row, slot, 'right')"
                                            type="button"
                                            class="slot-geo-btn"
                                            @click.stop="moveSlotInRow(row, slot, 'right')"
                                            title="Décaler vers la droite"
                                        >→</button>
                                        <button
                                            v-if="canSubdivideSlot(slot)"
                                            type="button"
                                            class="slot-geo-btn slot-geo-btn-split"
                                            @click.stop="subdivideSlot(row, slot)"
                                            title="Diviser ce Grand format en 4 Petits formats"
                                        >⊞ ×4</button>
                                        <button
                                            v-if="findMergeGroup(row, slot)"
                                            type="button"
                                            class="slot-geo-btn slot-geo-btn-merge"
                                            @click.stop="mergeSlots(row, slot)"
                                            title="Fusionner les 4 Petits formats en 1 Grand format"
                                        >⬜ Grand</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Bouton d'ajout de ligne en fin de galerie -->
                <div class="admin-add-row-bottom">
                    <button
                        type="button"
                        class="btn-add-harmonic-row"
                        @click="openAddRowModal(compositionRows.length)"
                    >
                        ➕ Ajouter une ligne harmonique
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="empty-state">
            <div class="empty-state-icon" style="display: flex; justify-content: center;">
                <PhImages :size="54" weight="thin" class="text-muted" />
            </div>
            <div class="empty-state-title">La galerie est vide</div>
            <div class="empty-state-text">Commencez par ajouter des photos à l'aide de la zone d'importation.</div>
        </div>

        <!-- Edit Modal -->
        <AdminModal :show="!!editingPhoto" @close="editingPhoto = null" title="Propriétés de la photo">
            <form v-if="editingPhoto" @submit.prevent="savePhoto">
                <div style="margin-bottom: var(--space-lg); text-align: center;">
                    <img :src="`/storage/${editingPhoto.thumbnail_path || editingPhoto.image_path}`" style="max-height: 220px; border-radius: var(--radius-md); object-fit: contain;" />
                </div>

                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <PremiumSelect 
                        v-model="photoForm.category_id"
                        :options="categories"
                    />
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

                <div style="margin-bottom: var(--space-xl);">
                    <label style="display: flex; align-items: center; gap: var(--space-sm); cursor: pointer;">
                        <input type="checkbox" v-model="photoForm.is_highlighted" style="width: auto; accent-color: var(--primary);" />
                        Mettre en avant (Coup de cœur accueil)
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

        <!-- Bulk Category Modal -->
        <AdminModal :show="showBulkCategoryModal" @close="showBulkCategoryModal = false" title="Modifier la catégorie groupée">
            <div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: var(--space-md);">
                    Attribuer une catégorie aux <strong>{{ selectedPhotoIds.length }}</strong> photos sélectionnées.
                </p>
                <div class="form-group">
                    <label class="form-label">Catégorie</label>
                    <PremiumSelect 
                        v-model="bulkCategoryId"
                        :options="categories"
                        placeholder="Choisir une catégorie"
                    />
                </div>
                <div style="display: flex; gap: var(--space-sm); justify-content: flex-end; margin-top: var(--space-lg);">
                    <button type="button" class="btn btn-ghost" @click="showBulkCategoryModal = false">Annuler</button>
                    <button type="button" class="btn btn-primary" @click="applyBulkCategory">
                        Appliquer aux {{ selectedPhotoIds.length }} photos
                    </button>
                </div>
            </div>
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

        <!-- Modale Spacieuse de sélection de Ligne Harmonique -->
        <AdminModal :show="showAddRowModal" @close="showAddRowModal = false" title="Choisir une ligne harmonique" maxWidth="1600px">
            <div style="padding: 4px;">
                <div class="harmonic-modal-grid">
                    <div
                        v-for="tpl in HARMONY_TEMPLATES"
                        :key="tpl.id"
                        class="harmonic-modal-card"
                        @click="applyHarmonyTemplate(tpl, addRowTargetIndex)"
                    >
                        <!-- Dessin géométrique fidèle du modèle (grille 12 colonnes) -->
                        <div class="harmonic-card-preview-area">
                            <div 
                                class="harmonic-card-mini-grid"
                                :style="{
                                    gridTemplateRows: `repeat(${tpl.totalRows}, 15px)`
                                }"
                            >
                                <div
                                    v-for="(slot, sIdx) in tpl.slots"
                                    :key="sIdx"
                                    class="harmonic-card-slot"
                                    :class="[
                                        `slot-w-${slot.span_cols}`,
                                        (slot.span_cols >= slot.span_rows) ? 'is-landscape-slot' : 'is-portrait-slot'
                                    ]"
                                    :style="{
                                        gridColumn: `${slot.col_start} / span ${slot.span_cols}`,
                                        gridRow: `${slot.row_start} / span ${slot.span_rows}`,
                                    }"
                                >
                                    <span class="slot-badge-text">{{ (slot.span_cols >= slot.span_rows) ? 'Paysage' : 'Portrait' }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="harmonic-card-info">
                            <div class="harmonic-card-title">{{ tpl.title }}</div>
                            <div class="harmonic-card-footer">
                                <span class="harmonic-card-badge">{{ tpl.photosCount }} photos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminModal>
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

/* Templates Toggle Button */
.btn-templates-toggle {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.35);
    color: #c7d2fe;
    padding: 0.38rem 0.85rem;
    border-radius: var(--radius-full);
    font-size: 0.82rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-templates-toggle:hover {
    background: rgba(99, 102, 241, 0.25);
    border-color: rgba(99, 102, 241, 0.6);
    color: white;
}

.btn-templates-toggle.active {
    background: var(--primary);
    border-color: var(--primary-light);
    color: white;
    box-shadow: 0 0 12px var(--primary-glow);
}

/* Templates Drawer Panel */
.templates-drawer-panel {
    margin-top: var(--space-md);
    padding-top: var(--space-md);
    border-top: 1px solid var(--border-color);
    animation: fadeIn 0.3s ease;
}

.templates-drawer-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: var(--space-xs);
    margin-bottom: var(--space-md);
}

.templates-grid-scroll {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: var(--space-md);
}

.template-card-preview {
    background: var(--bg-elevated);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.template-card-preview:hover {
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35);
}

/* Dessin filaire géométrique de la ligne */
.template-mini-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 3px;
    background: rgba(0, 0, 0, 0.35);
    padding: 6px;
    border-radius: var(--radius-sm);
    border: 1px solid rgba(255, 255, 255, 0.08);
    margin-bottom: 8px;
}

.template-mini-slot {
    background: rgba(99, 102, 241, 0.25);
    border: 1px solid rgba(99, 102, 241, 0.55);
    border-radius: 2px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #e0e7ff;
    font-size: 0.6rem;
    font-weight: 700;
    transition: all 0.2s ease;
}

.template-card-preview:hover .template-mini-slot {
    background: rgba(99, 102, 241, 0.45);
    border-color: var(--primary-light);
}

.template-card-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.template-card-name {
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--text-color);
}

.template-card-badge {
    font-size: 0.68rem;
    font-weight: 700;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.3);
    color: #c7d2fe;
    padding: 0.1rem 0.45rem;
    border-radius: var(--radius-full);
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

/* Lignes de composition déplaçables */
.admin-mosaic-row-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.45rem 0.85rem;
    background: linear-gradient(90deg, rgba(30, 41, 59, 0.95), rgba(15, 23, 42, 0.9));
    border: 1px dashed rgba(99, 102, 241, 0.45);
    border-radius: var(--radius-sm);
    z-index: 10;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.35);
    user-select: none;
    margin-bottom: 6px;
    height: 38px;
    align-self: start;
}

.row-bar-info {
    display: flex;
    align-items: center;
    gap: 8px;
}

.row-bar-tag {
    font-size: 0.75rem;
    font-weight: 700;
    color: #e0e7ff;
    background: rgba(99, 102, 241, 0.25);
    border: 1px solid rgba(99, 102, 241, 0.5);
    padding: 0.15rem 0.55rem;
    border-radius: var(--radius-full);
}

.row-bar-count {
    font-size: 0.72rem;
    color: var(--text-muted);
}

.row-bar-actions {
    display: flex;
    align-items: center;
    gap: 6px;
}

.row-bar-btn {
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(255, 255, 255, 0.08);
    color: #e0e7ff;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 0.22rem 0.55rem;
    border-radius: var(--radius-sm);
    cursor: pointer;
    transition: all 0.15s ease;
}

.row-bar-btn:hover:not(:disabled) {
    background: var(--primary);
    border-color: var(--primary-light);
    color: white;
    transform: translateY(-1px);
}

.row-bar-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

/* --- Admin Mosaic Grid (Row-based container) --- */
.admin-mosaic-grid {
    display: flex;
    flex-direction: column;
    gap: 20px;
    width: 100%;
}

.admin-composition-row-block {
    background: rgba(15, 23, 42, 0.45);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: var(--radius-md);
    padding: var(--space-sm);
    transition: all 0.2s ease;
}

.admin-composition-row-block.is-row-dragging {
    opacity: 0.35;
    border-color: var(--primary);
    transform: scale(0.99);
}

.admin-composition-row-block.is-row-drag-over {
    border-color: var(--primary-light);
    box-shadow: 0 0 20px var(--primary-glow);
    background: rgba(99, 102, 241, 0.08);
}

.admin-mosaic-row-grid {
    display: grid;
    grid-template-columns: repeat(var(--grid-cols, 12), 1fr);
    grid-auto-flow: dense;
    gap: 16px;
    width: 100%;
}

.row-drag-handle {
    cursor: grab;
    padding: 2px 6px;
    font-size: 1rem;
    color: var(--text-muted);
    border-radius: var(--radius-sm);
    background: rgba(255, 255, 255, 0.06);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    user-select: none;
    transition: all 0.15s ease;
}

.row-drag-handle:hover {
    color: white;
    background: rgba(255, 255, 255, 0.15);
}

.row-drag-handle:active {
    cursor: grabbing;
}

.row-bar-btn-add {
    background: rgba(99, 102, 241, 0.25) !important;
    border-color: rgba(99, 102, 241, 0.5) !important;
    color: #e0e7ff !important;
}

.row-bar-btn-add:hover {
    background: var(--primary) !important;
    color: white !important;
    border-color: var(--primary-light) !important;
}

/* Modale de sélection de Ligne Harmonique */
.harmonic-modal-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 16px;
    padding: 10px 4px;
}

@media (max-width: 1400px) {
    .harmonic-modal-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    }
}

.harmonic-modal-card {
    background: var(--bg-deep);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: var(--radius-lg);
    padding: var(--space-md);
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.harmonic-modal-card:hover {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.15);
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.6), 0 0 16px var(--primary-glow);
    z-index: 2;
}

.harmonic-card-preview-area {
    height: 145px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.45);
    border-radius: var(--radius-md);
    border: 1px solid rgba(255, 255, 255, 0.08);
    padding: 8px;
    margin-bottom: var(--space-sm);
}

.harmonic-card-mini-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 2px;
    width: 100%;
}

.harmonic-card-slot {
    background: rgba(99, 102, 241, 0.3);
    border: 1px solid rgba(129, 140, 248, 0.7);
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 0.72rem;
    font-weight: 700;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.9);
    overflow: hidden;
    padding: 2px;
    transition: all 0.15s ease;
}

.harmonic-card-slot.is-landscape-slot {
    background: rgba(59, 130, 246, 0.3);
    border-color: rgba(96, 165, 250, 0.8);
}

.harmonic-card-slot.is-portrait-slot {
    background: rgba(168, 85, 247, 0.3);
    border-color: rgba(192, 132, 252, 0.8);
}

.harmonic-modal-card:hover .harmonic-card-slot.is-landscape-slot {
    background: rgba(59, 130, 246, 0.55);
    border-color: #bfdbfe;
}

.harmonic-modal-card:hover .harmonic-card-slot.is-portrait-slot {
    background: rgba(168, 85, 247, 0.55);
    border-color: #e9d5ff;
}

.slot-badge-text {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 100%;
}

.harmonic-card-slot.slot-w-2 .slot-badge-text {
    font-size: 0.65rem;
    letter-spacing: -0.3px;
}

.harmonic-card-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.harmonic-card-title {
    font-size: 0.88rem;
    font-weight: 700;
    color: #f8fafc;
    line-height: 1.3;
    min-height: 2.4em;
    display: flex;
    align-items: center;
}

.harmonic-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
}

.harmonic-card-badge {
    font-size: 0.75rem;
    font-weight: 700;
    background: rgba(99, 102, 241, 0.25);
    border: 1px solid rgba(99, 102, 241, 0.5);
    color: #e0e7ff;
    padding: 0.18rem 0.6rem;
    border-radius: var(--radius-full);
}

/* --- Bac de photos en attente de placement (Dock) --- */
.unplaced-photos-dock {
    background: var(--surface-color);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-lg);
    padding: var(--space-md) var(--space-lg);
    margin-bottom: var(--space-xl);
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.25);
    transition: border-color 0.2s ease;
}

.dock-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-md);
    flex-wrap: wrap;
    gap: var(--space-md);
}

.dock-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 700;
    color: #f1f5f9;
    font-size: 0.95rem;
}

.dock-actions {
    display: flex;
    align-items: center;
    gap: var(--space-md);
}

.dock-hint {
    font-size: 0.8rem;
    color: var(--text-muted);
}

.dock-btn-auto {
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.4);
    color: #c7d2fe;
    padding: 0.35rem 0.75rem;
    border-radius: var(--radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all var(--transition-fast);
}

.dock-btn-auto:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

.dock-photos-strip {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding-bottom: 4px;
}

.dock-photo-item {
    position: relative;
    flex-shrink: 0;
    width: 90px;
    height: 70px;
    border-radius: var(--radius-sm);
    overflow: hidden;
    cursor: grab;
    border: 2px solid rgba(255, 255, 255, 0.1);
    transition: all 0.2s ease;
    background: #000;
}

.dock-photo-item:hover {
    border-color: var(--primary-light);
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(0, 0, 0, 0.5);
}

.dock-photo-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.dock-photo-badge {
    position: absolute;
    bottom: 2px;
    left: 2px;
    right: 2px;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
    font-size: 0.6rem;
    font-weight: 700;
    color: #e2e8f0;
    text-align: center;
    padding: 1px 0;
    border-radius: 2px;
}

/* --- Slots vides (Emplacements en attente dans une ligne) --- */
.admin-mosaic-empty-slot {
    border: 2px dashed rgba(255, 255, 255, 0.18);
    border-radius: var(--radius-md);
    background: rgba(255, 255, 255, 0.02);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    min-height: 80px;
    cursor: pointer;
}

.admin-mosaic-empty-slot:hover,
.admin-mosaic-empty-slot.is-drag-over {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.12);
    box-shadow: inset 0 0 16px rgba(99, 102, 241, 0.2);
    transform: scale(0.995);
}

.empty-slot-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    text-align: center;
    padding: 8px;
    pointer-events: none;
}

.empty-slot-icon {
    font-size: 1.3rem;
}

.empty-slot-label {
    font-size: 0.8rem;
    font-weight: 700;
    color: #cbd5e1;
}

.empty-slot-sub {
    font-size: 0.7rem;
    color: var(--text-muted);
}

/* --- Contrôles géométriques intra-ligne (Déplacement ← ↑ ↓ → & Division/Fusion) --- */
.slot-geometry-actions {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    pointer-events: auto;
}

.empty-slot-geo-actions {
    margin-top: 6px;
    justify-content: center;
}

.slot-geo-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 26px;
    height: 24px;
    padding: 0 6px;
    border-radius: 5px;
    border: 1px solid rgba(255, 255, 255, 0.22);
    background: rgba(15, 23, 42, 0.85);
    color: #f8fafc;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.15s ease;
    line-height: 1;
}

.slot-geo-btn:hover {
    background: var(--primary);
    border-color: var(--primary-light);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(99, 102, 241, 0.4);
}

.slot-geo-btn-split {
    background: rgba(59, 130, 246, 0.28);
    border-color: rgba(96, 165, 250, 0.55);
    color: #93c5fd;
    font-size: 0.72rem;
}

.slot-geo-btn-split:hover {
    background: #2563eb;
    border-color: #60a5fa;
    color: #fff;
}

.slot-geo-btn-merge {
    background: rgba(168, 85, 247, 0.28);
    border-color: rgba(192, 132, 252, 0.55);
    color: #d8b4fe;
    font-size: 0.72rem;
}

.slot-geo-btn-merge:hover {
    background: #9333ea;
    border-color: #c084fc;
    color: #fff;
}

.empty-rows-banner {
    background: rgba(15, 23, 42, 0.4);
    border: 1px dashed rgba(255, 255, 255, 0.15);
    border-radius: var(--radius-lg);
    padding: var(--space-2xl);
    text-align: center;
}

.empty-rows-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-bottom: var(--space-xs);
}

.empty-rows-text {
    font-size: 0.9rem;
    color: var(--text-muted);
}

.row-bar-btn-danger {
    color: #fca5a5 !important;
    border-color: rgba(239, 68, 68, 0.3) !important;
}

.row-bar-btn-danger:hover {
    background: rgba(239, 68, 68, 0.2) !important;
    border-color: #ef4444 !important;
    color: white !important;
}

.btn-icon-detach {
    font-size: 0.9rem;
    color: #94a3b8;
}

.btn-icon-detach:hover {
    color: #f1f5f9;
    background: rgba(255, 255, 255, 0.15);
}

.admin-add-row-bottom {
    display: flex;
    justify-content: center;
    margin: var(--space-xl) 0 var(--space-md);
}

.btn-add-harmonic-row {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(99, 102, 241, 0.2), rgba(168, 85, 247, 0.25));
    border: 1px dashed rgba(99, 102, 241, 0.6);
    color: #e0e7ff;
    padding: 0.75rem 1.75rem;
    border-radius: var(--radius-full);
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-add-harmonic-row:hover,
.btn-add-harmonic-row.active {
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-color: transparent;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px var(--primary-glow);
}

.templates-modal-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: var(--space-md);
    max-height: 65vh;
    overflow-y: auto;
    padding: 4px;
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

/* Compact Grid */
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

.photo-compact-card.is-highlighted {
    border-color: var(--primary);
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
</style>
