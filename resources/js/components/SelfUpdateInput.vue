<script setup lang="ts">
import axios from 'axios';
import { ref, watch } from 'vue';
import { Input } from '@/components/ui/input';

interface Props {
    url: string;
    column: string;
    modelValue: string | number;
    data?: object;
    type?: 'text' | 'number' | 'time' | 'date';
    method?: 'post' | 'patch' | 'put' | 'delete';
}

const props = withDefaults(defineProps<Props>(), {
    method: 'patch',
    type: 'text',
    data: () => ({}),
});
const emit = defineEmits<{
    'update:modelValue': [value: string | number];
}>();

const localValue = ref(props.modelValue);
const isProcessing = ref(false);
const borderState = ref<'default' | 'success' | 'error'>('default');
let timeoutId: ReturnType<typeof setTimeout> | null = null;

watch(
    () => props.modelValue,
    (newValue) => {
        localValue.value = newValue;
    },
);

const handleBlur = async () => {
    if (localValue.value === props.modelValue || isProcessing.value) {
        return;
    }

    const oldValue = props.modelValue;
    isProcessing.value = true;
    borderState.value = 'default';

    if (timeoutId) {
        clearTimeout(timeoutId);
        timeoutId = null;
    }

    const data = { ...props.data };
    data[props.column] = localValue.value;

    try {
        await axios[props.method](props.url, data);

        emit('update:modelValue', localValue.value);
        borderState.value = 'success';

        timeoutId = setTimeout(() => {
            borderState.value = 'default';
            timeoutId = null;
        }, 1000);
    } catch (error: unknown | any) {
        localValue.value = oldValue;
        borderState.value = 'error';

        timeoutId = setTimeout(() => {
            borderState.value = 'default';
            timeoutId = null;
        }, 1000);
    } finally {
        isProcessing.value = false;
    }
};
</script>

<template>
    <Input
        v-model="localValue"
        :type="type"
        :disabled="isProcessing"
        :class="[
            'transition-all duration-300',
            {
                'border-green-500 ring-2 ring-green-200':
                    borderState === 'success',
                'border-red-500 ring-2 ring-red-200': borderState === 'error',
                'appearance-none [&::-webkit-calendar-picker-indicator]:hidden [&::-webkit-calendar-picker-indicator]:appearance-none':
                    type === 'time' || type === 'date',
            },
        ]"
        @blur="handleBlur"
    />
</template>

<style scoped>
@keyframes pulse-success {
    0%,
    100% {
        border-color: rgb(34 197 94);
        box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.2);
    }
    50% {
        border-color: rgb(34 197 94);
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.4);
    }
}

@keyframes pulse-error {
    0%,
    100% {
        border-color: rgb(239 68 68);
        box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.2);
    }
    50% {
        border-color: rgb(239 68 68);
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.4);
    }
}

.border-green-500.ring-2 {
    animation: pulse-success 1s ease-in-out;
}

.border-red-500.ring-2 {
    animation: pulse-error 1s ease-in-out;
}
</style>
