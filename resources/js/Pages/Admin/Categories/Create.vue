<script setup lang="ts">
import Breadcrumb from '@/Components/Breadcrumb.vue';
import Button from '@/Components/Button.vue';
import Card from '@/Components/Card.vue';
import FormField from '@/Components/FormField.vue';
import Input from '@/Components/Input.vue';
import type { SelectOption } from '@/Components/Select.vue';
import Select from '@/Components/Select.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    parentOptions: SelectOption[];
}>();

const form = useForm({
    name: '',
    parent_id: null as string | null,
});

function submit() {
    form.post(route('admin.categories.store'));
}
</script>

<template>
    <Head title="Tambah Kategori" />

    <AdminLayout>
        <div class="space-y-4">
            <Breadcrumb
                :items="[
                    {
                        label: 'Kategori',
                        href: route('admin.categories.index'),
                    },
                    { label: 'Tambah' },
                ]"
            />

            <Card class="max-w-xl">
                <form class="space-y-4" @submit.prevent="submit">
                    <FormField
                        label="Nama Kategori"
                        :error="form.errors.name"
                        required
                    >
                        <Input
                            id="name"
                            v-model="form.name"
                            autofocus
                            :invalid="!!form.errors.name"
                        />
                    </FormField>

                    <FormField
                        label="Parent (opsional)"
                        :error="form.errors.parent_id"
                        helper-text="Kosongkan untuk jadikan kategori utama (root)."
                    >
                        <Select
                            v-model="form.parent_id"
                            placeholder="Tidak ada (kategori utama)"
                            :options="parentOptions"
                            :invalid="!!form.errors.parent_id"
                        />
                    </FormField>

                    <div class="flex justify-end gap-3 pt-2">
                        <Button type="submit" :loading="form.processing"
                            >Simpan</Button
                        >
                    </div>
                </form>
            </Card>
        </div>
    </AdminLayout>
</template>
