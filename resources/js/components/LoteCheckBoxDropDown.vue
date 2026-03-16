<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useControllable } from '@/composables/useControllable';

type Option = {
    label: string;
    value: boolean;
};

const props = defineProps({
    modelValue: Array,
    defaultValue: Array,
    triggerLabel: {
        type: String,
        default: 'Select',
    },
});

const emit = defineEmits(['update:modelValue']);
const value = useControllable(props, emit);

const update = (option: Option, checked: boolean | 'indeterminate') => {
    value.value = value.value.map((item: Option) => {
        if (item.label === option.label) {
            return { ...item, value: checked === true };
        }

        return item;
    });
};
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <slot name="trigger">
                <Button>{{ triggerLabel }}</Button>
            </slot>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="p-4" align="end">
            <div class="flex flex-col gap-2">
                <label
                    v-for="option in value"
                    :key="option.label"
                    class="flex items-center gap-2"
                >
                    <Checkbox
                        :model-value="option.value"
                        @update:modelValue="
                            (checked) => update(option, checked)
                        "
                    />
                    {{ option.label }}
                </label>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
