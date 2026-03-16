<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    // DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { ref, watch } from 'vue';

defineProps({
    title: {
        type: String,
        default: null,
    },
    description: {
        type: String,
        default: null,
    },
    dialogContentClasses: {
        type: String,
        default: '',
    },
});

const isOpen = ref(false);
const emitData = ref(null);

const closeModal = (data = null) => {
    emitData.value = data;
    isOpen.value = false;
};
const openModal = (data = null) => {
    emitData.value = data;
    isOpen.value = true;
};

const emit = defineEmits(['close', 'open']);

watch(isOpen, () => {
    if (isOpen.value) {
        emit('open', emitData.value);
    } else {
        emit('close', emitData.value);
    }
});

defineExpose({
    openModal,
    closeModal,
});
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger>
            <slot />
        </DialogTrigger>
        <DialogContent :class="dialogContentClasses">
            <DialogHeader>
                <DialogTitle>
                    <slot name="title" v-if="!title"></slot>
                    <template v-else>{{ title }}</template>
                </DialogTitle>
                <DialogDescription>
                    <slot name="title" v-if="!description"></slot>
                    <template v-else>{{ description }}</template>
                </DialogDescription>
            </DialogHeader>
            <slot name="content" :closeModal="closeModal" :data="emitData" />
        </DialogContent>
    </Dialog>
</template>
