<template>
    <div>
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 mb-7">
            <div>
                <div class="text-caption font-weight-bold text-uppercase mb-1" style="color: #C6A15B; letter-spacing: 0.14em;">
                    Fleet Classification
                </div>
                <h1 class="font-display text-h4 font-weight-bold" style="color: #101D32;">
                    Vehicle Categories
                </h1>
                <p class="text-body-2 mt-1" style="color: #777777;">
                    Organize your luxury and executive fleet classifications.
                </p>
            </div>

            <v-btn
                color="secondary"
                prepend-icon="mdi-plus"
                variant="flat"
                class="font-weight-bold text-primary px-5"
                @click="openCreateDialog"
            >
                Add Category
            </v-btn>
        </div>

        <v-card class="luxury-card overflow-hidden">
            <v-card-title class="pa-4">
                <v-text-field
                    v-model="search"
                    prepend-inner-icon="mdi-magnify"
                    label="Search categories"
                    density="compact"
                    hide-details
                    clearable
                    style="max-width: 340px;"
                />
            </v-card-title>

            <v-data-table
                :headers="headers"
                :items="categories"
                :search="search"
                :loading="loading"
                item-value="id"
                hover
            >
                <template #item.name="{ item }">
                    <span class="font-weight-bold" style="color: #101D32;">{{ item.name }}</span>
                </template>

                <template #item.description="{ item }">
                    <span class="text-body-2" style="color: #777777;">
                        {{ item.description || '—' }}
                    </span>
                </template>

                <template #item.vehicles_count="{ item }">
                    <v-chip size="small" color="primary" variant="tonal" class="font-weight-medium">
                        {{ item.vehicles_count ?? 0 }} vehicle(s)
                    </v-chip>
                </template>

                <template #item.actions="{ item }">
                    <div class="d-flex justify-end ga-1">
                        <v-btn
                            icon="mdi-pencil-outline"
                            size="small"
                            variant="text"
                            color="primary"
                            title="Edit"
                            @click="openEditDialog(item)"
                        />
                        <v-btn
                            icon="mdi-delete-outline"
                            size="small"
                            variant="text"
                            color="error"
                            title="Delete"
                            @click="openDeleteDialog(item)"
                        />
                    </div>
                </template>
            </v-data-table>
        </v-card>

        <!-- Create / Edit Dialog -->
        <v-dialog v-model="formDialog" max-width="520" persistent>
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h5 font-weight-bold" style="color: #101D32;">
                    {{ editingId ? 'Edit Category' : 'Add Category' }}
                </v-card-title>
                <v-divider style="border-color: #EFECE6;" />

                <v-form ref="formRef" @submit.prevent="saveCategory">
                    <v-card-text class="pa-5">
                        <v-alert
                            v-if="formError"
                            type="error"
                            variant="tonal"
                            density="compact"
                            class="mb-4"
                        >
                            {{ formError }}
                        </v-alert>

                        <v-text-field
                            v-model="form.name"
                            label="Category Name *"
                            density="comfortable"
                            :rules="[(v) => !!v?.trim() || 'Category name is required.']"
                            class="mb-3"
                        />

                        <v-textarea
                            v-model="form.description"
                            label="Description"
                            density="comfortable"
                            rows="3"
                            auto-grow
                        />
                    </v-card-text>

                    <v-divider style="border-color: #EFECE6;" />
                    <v-card-actions class="pa-4">
                        <v-spacer />
                        <v-btn variant="text" color="primary" :disabled="saving" @click="formDialog = false">
                            Cancel
                        </v-btn>
                        <v-btn type="submit" color="primary" variant="flat" class="px-5" :loading="saving">
                            {{ editingId ? 'Update' : 'Create' }}
                        </v-btn>
                    </v-card-actions>
                </v-form>
            </v-card>
        </v-dialog>

        <!-- Delete Confirmation Dialog -->
        <v-dialog v-model="deleteDialog" max-width="420">
            <v-card class="luxury-card">
                <v-card-title class="pa-5 font-display text-h6 font-weight-bold" style="color: #101D32;">
                    Delete Category
                </v-card-title>
                <v-card-text class="px-5 pb-4">
                    Are you sure you want to delete <strong>{{ selectedCategory?.name }}</strong>?
                </v-card-text>
                <v-card-actions class="pa-4">
                    <v-spacer />
                    <v-btn variant="text" color="primary" :disabled="deleting" @click="deleteDialog = false">
                        Cancel
                    </v-btn>
                    <v-btn color="error" variant="flat" :loading="deleting" @click="confirmDelete">
                        Delete
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
            {{ snackbar.text }}
        </v-snackbar>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import api from '@/plugins/axios';

const categories = ref([]);
const loading = ref(false);
const search = ref('');

const formDialog = ref(false);
const deleteDialog = ref(false);
const formRef = ref(null);
const editingId = ref(null);
const selectedCategory = ref(null);
const saving = ref(false);
const deleting = ref(false);
const formError = ref('');

const form = reactive({
    name: '',
    description: '',
});

const snackbar = reactive({
    show: false,
    text: '',
    color: 'success',
});

const headers = [
    { title: 'Name', key: 'name' },
    { title: 'Description', key: 'description', sortable: false },
    { title: 'Vehicles', key: 'vehicles_count', align: 'center' },
    { title: 'Actions', key: 'actions', sortable: false, align: 'end' },
];

function notify(text, color = 'success') {
    snackbar.text = text;
    snackbar.color = color;
    snackbar.show = true;
}

async function fetchCategories() {
    loading.value = true;
    try {
        const { data } = await api.get('/admin/categories');
        categories.value = data;
    } catch (error) {
        notify(error.response?.data?.message || 'Failed to load categories.', 'error');
    } finally {
        loading.value = false;
    }
}

function openCreateDialog() {
    editingId.value = null;
    form.name = '';
    form.description = '';
    formError.value = '';
    formDialog.value = true;
}

function openEditDialog(item) {
    editingId.value = item.id;
    form.name = item.name;
    form.description = item.description || '';
    formError.value = '';
    formDialog.value = true;
}

function openDeleteDialog(item) {
    selectedCategory.value = item;
    deleteDialog.value = true;
}

async function saveCategory() {
    formError.value = '';
    const { valid } = await formRef.value.validate();
    if (!valid) return;

    saving.value = true;
    try {
        const payload = {
            name: form.name.trim(),
            description: form.description?.trim() || null,
        };

        if (editingId.value) {
            await api.put(`/admin/categories/${editingId.value}`, payload);
            notify('Category updated successfully.');
        } else {
            await api.post('/admin/categories', payload);
            notify('Category created successfully.');
        }

        formDialog.value = false;
        await fetchCategories();
    } catch (error) {
        formError.value = error.response?.data?.message || 'Unable to save category.';
    } finally {
        saving.value = false;
    }
}

async function confirmDelete() {
    if (!selectedCategory.value) return;
    deleting.value = true;
    try {
        await api.delete(`/admin/categories/${selectedCategory.value.id}`);
        deleteDialog.value = false;
        notify('Category deleted successfully.');
        await fetchCategories();
    } catch (error) {
        deleteDialog.value = false;
        notify(error.response?.data?.message || 'Failed to delete category.', 'error');
    } finally {
        deleting.value = false;
    }
}

onMounted(fetchCategories);
</script>
