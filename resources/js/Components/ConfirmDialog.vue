<script setup lang="ts">
import Button from '@/Components/Button.vue';
import Modal from '@/Components/Modal.vue';
import { TriangleAlert } from '@lucide/vue';

// Dialog konfirmasi generik untuk aksi berisiko (void transaksi, hapus
// produk, tutup shift dengan selisih) — lihat docs/UI.md prinsip desain
// "Konfirmasi untuk aksi berisiko".
withDefaults(
    defineProps<{
        show: boolean;
        title: string;
        message: string;
        confirmLabel?: string;
        cancelLabel?: string;
        danger?: boolean;
        processing?: boolean;
    }>(),
    {
        confirmLabel: 'Konfirmasi',
        cancelLabel: 'Batal',
        danger: false,
        processing: false,
    },
);

const emit = defineEmits<{
    confirm: [];
    cancel: [];
}>();
</script>

<template>
    <Modal :show="show" max-width="sm" @close="emit('cancel')">
        <div class="p-6">
            <div class="flex items-start gap-3">
                <div
                    v-if="danger"
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-danger/10"
                >
                    <TriangleAlert
                        class="h-5 w-5 text-danger"
                        aria-hidden="true"
                    />
                </div>

                <div>
                    <h2 class="text-base font-semibold text-text">
                        {{ title }}
                    </h2>
                    <p class="mt-1 text-sm text-text-muted">{{ message }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <Button
                    variant="outline"
                    :disabled="processing"
                    @click="emit('cancel')"
                >
                    {{ cancelLabel }}
                </Button>
                <Button
                    :variant="danger ? 'danger' : 'primary'"
                    :loading="processing"
                    @click="emit('confirm')"
                >
                    {{ confirmLabel }}
                </Button>
            </div>
        </div>
    </Modal>
</template>
