<script setup lang="ts">
import { onMounted, ref, watch } from 'vue';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

const props = defineProps({
    options: [Array, Object],
    type: {
        type: String as () => 'single' | 'multiple',
        default: 'multiple',
    },
    selected: {
        type: [Array, String],
    },
    variant: {
        type: String as () => 'outline' | 'default' | 'primary',
        default: 'outline',
    },
    itemClasses: {
        type: String,
        default: '',
    },
});

const modelValue = ref(props.selected);

const emit = defineEmits(['update:selected']);

watch(
    () => props.selected,
    (newValue) => {
        modelValue.value = newValue;
    },
);

onMounted(() => {
    modelValue.value = props.selected;
});
</script>

<template>
    <ToggleGroup :type="type" :variant="variant" v-model="modelValue" @update:modelValue="(val) => emit('update:selected', val)">
        <ToggleGroupItem v-for="option in options" :value="option.value" :key="option.value" :disabled="option.disabled" :class="itemClasses">
            {{ option.label }}
        </ToggleGroupItem>
    </ToggleGroup>
</template>
