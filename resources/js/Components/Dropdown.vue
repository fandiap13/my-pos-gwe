<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        align?: 'left' | 'right';
        width?: '48';
        contentClasses?: string;
    }>(),
    {
        align: 'right',
        width: '48',
        contentClasses: 'py-1 bg-surface',
    },
);

const open = ref(false);
const triggerRef = ref<HTMLElement | null>(null);
const menuRef = ref<HTMLElement | null>(null);
const menuStyle = ref<Record<string, string>>({});

const widthClass = computed(() => {
    return {
        48: 'w-48',
    }[props.width.toString()];
});

// Hanya origin untuk animasi scale — posisi (left/right/top) dihitung
// sendiri via menuStyle, bukan lewat class, karena menu sekarang fixed.
const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'ltr:origin-top-left rtl:origin-top-right';
    } else {
        return 'ltr:origin-top-right rtl:origin-top-left';
    }
});

function removePositionListeners() {
    window.removeEventListener('scroll', updatePosition, true);
    window.removeEventListener('resize', updatePosition);
}

function updatePosition() {
    const trigger = triggerRef.value;
    if (!trigger) {
        return;
    }

    const rect = trigger.getBoundingClientRect();
    const menuHeight = menuRef.value?.offsetHeight ?? 0;
    const style: Record<string, string> = {};

    // Buka ke atas kalau ruang di bawah trigger tidak cukup, supaya menu
    // baris terakhir tidak terpotong tepi viewport.
    const spaceBelow = window.innerHeight - rect.bottom - 8;
    if (
        menuHeight > 0 &&
        menuHeight > spaceBelow &&
        rect.top - 8 > menuHeight
    ) {
        style.bottom = `${window.innerHeight - rect.top + 8}px`;
    } else {
        style.top = `${rect.bottom + 8}px`;
    }

    if (props.align === 'right') {
        style.right = `${document.documentElement.clientWidth - rect.right}px`;
    } else {
        style.left = `${rect.left}px`;
    }

    menuStyle.value = style;
}

async function setOpen(value: boolean) {
    if (value === open.value) {
        return;
    }

    if (value) {
        // Posisi awal dulu (tanpa flip), supaya menu tidak sesaat muncul
        // di pojok viewport sebelum layout-nya terbaca.
        updatePosition();
    } else {
        removePositionListeners();
    }

    open.value = value;

    if (value) {
        await nextTick();
        updatePosition();
        window.addEventListener('scroll', updatePosition, {
            capture: true,
            passive: true,
        });
        window.addEventListener('resize', updatePosition);
    }
}

function toggle() {
    setOpen(!open.value);
}

const closeOnEscape = (e: KeyboardEvent) => {
    if (open.value && e.key === 'Escape') {
        setOpen(false);
    }
};

onMounted(() => window.addEventListener('keydown', closeOnEscape));
onUnmounted(() => {
    window.removeEventListener('keydown', closeOnEscape);
    removePositionListeners();
});
</script>

<template>
    <!--
        Trigger & overlay harus sibling (bukan induk–anak): kalau overlay
        jadi anak elemen trigger, click-nya bocor ke handler toggle dan
        menu langsung terbuka lagi setelah ditutup dari luar.
    -->
    <div class="relative">
        <div ref="triggerRef" @click="toggle">
            <slot name="trigger" />
        </div>

        <!-- Full Screen Dropdown Overlay -->
        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="setOpen(false)"
        ></div>

        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-200"
                enter-from-class="opacity-0 scale-95"
                enter-to-class="opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
            >
                <div
                    v-show="open"
                    ref="menuRef"
                    class="fixed z-50 rounded-control shadow-lg"
                    :class="[widthClass, alignmentClasses]"
                    :style="menuStyle"
                    @click="setOpen(false)"
                >
                    <div
                        class="rounded-control border border-border"
                        :class="contentClasses"
                    >
                        <slot name="content" />
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>
