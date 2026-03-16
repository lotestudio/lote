<script setup lang="ts">
import {
    SelectArrow,
    SelectContent,
    SelectIcon,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    SelectPortal,
    SelectRoot,
    SelectScrollDownButton,
    SelectScrollUpButton,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'reka-ui';

import { computed, nextTick, onMounted, ref, watch } from 'vue';

const EMPTY_VALUE_MARKER = '__EMPTY_SELECTION__';

const props = defineProps({
    options: {
        type: Array,
        required: true,
    },
    disabled:{
        type: Array,
        default:()=>[]
    },
    selected: {
        type: [String, Number],
        default: null,
    },
    width_class: {
        type: String,
        default: 'w-72',
    },
    // @deprecated
    dropdown_width_class: {
        type: String,
        default: null,
    },
    name: {
        type: String,
        default: null,
    },
    notFoundValueLabel: {
        type: String,
        default: 'N/A',
    },
    notFoundValueClass: {
        type: String,
        default: 'text-danger-500',
    },
    doNotShowChevrons: {
        type: Boolean,
        default: false,
    },
    listBoxButtonClass: {
        type: String,
        default: 'defaultListBoxButtonClass',
    },
    tabindex: {
        type: [String, Number],
        default: 0,
    },
});

// Reactive reference for the selected value
const selectedRef = ref(null);
const emit = defineEmits(['change']);

const update = (event) => {
    nextTick(() => {
        const valueToEmit = event === EMPTY_VALUE_MARKER ? '' : event;
        emit('change', valueToEmit ?? '');
    });
};

// Preprocess the option array: remove empty value
const filteredOptions = computed(() => {
    return props.options.map((option) => {
        // Създаваме нов обект, за да не мутираме пропса
        const newOption = { ...option };
        // Ако стойността е празна или null, сменяме я с маркера
        if (newOption.value === '' || newOption.value === null) {
            newOption.value = EMPTY_VALUE_MARKER;
        }
        return newOption;
    });
});

const isMounted = ref(false);

// Set the initial value for selectedRef on mount
onMounted(() => {
    const initialVal = props.selected === '' || props.selected === null ? EMPTY_VALUE_MARKER : props.selected;
    selectedRef.value = filteredOptions.value.find((option) => option.value === initialVal)?.value ?? null;
    isMounted.value = true;
});

const selectedLabel = computed(() => {
    return filteredOptions.value.find((option) => option.value === selectedRef.value)?.label ?? props.notFoundValueLabel;
});

//Chek if props.selected is changed after mounting. TODO::NOT TESTED
watch(
    () => props.selected,
    (newVal) => {
        if (isMounted.value) {
            // При промяна отвън, отново прилагаме логиката за маркера
            selectedRef.value = (newVal === '' || newVal === null) ? EMPTY_VALUE_MARKER : newVal;
        }
    },
);

</script>
<template>
    <SelectRoot v-model="selectedRef" :name="name" @update:modelValue="update">
        <SelectTrigger :class="[props.width_class, props.listBoxButtonClass]" :tabindex="props.tabindex">
            <SelectValue class="block truncate">
                {{ selectedLabel }}
            </SelectValue>
            <SelectIcon v-if="!props.doNotShowChevrons" as-child>
                <span class="i-chevronUpDown"></span>
            </SelectIcon>
        </SelectTrigger>

        <SelectPortal>
            <SelectContent class="z-[100] min-w-[160px] rounded-lg border bg-background p-2 text-sm shadow-sm">
                <SelectScrollUpButton class="flex h-[25px] cursor-default items-center justify-center rounded-b-lg bg-background">
                    <span class="i-angleUp"></span>
                </SelectScrollUpButton>
                <SelectViewport class="p-1">
                    <SelectItem
                        v-for="(option) in filteredOptions"
                        :key="option.value"
                        :value="option.value"
                        class="block cursor-pointer rounded p-2 outline-none hover:bg-accent focus:bg-accent focus:text-accent-foreground"
                        :title="option.label"
                        :disabled="disabled.includes(option.value)"
                    >
                        <SelectItemIndicator class="absolute left-0 inline-flex w-[25px] items-center justify-center">
                            <span class="i-check"></span>
                        </SelectItemIndicator>
                        <SelectItemText :class="disabled.includes(option.value) ? 'text-foreground/50':''">{{ option.label }}</SelectItemText>
                    </SelectItem>
                </SelectViewport>
                <SelectScrollDownButton class="flex h-[25px] cursor-default items-center justify-center rounded-b-lg bg-background">
                    <span class="i-angleDown"></span>
                </SelectScrollDownButton>
                <SelectArrow />
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>

<style scoped>
@reference "@@/app.css";

.defaultListBoxButtonClass {
    @apply relative flex cursor-default items-center justify-between rounded-md bg-background px-3 py-2 text-sm outline -outline-offset-1 outline-border focus:outline-accent-foreground focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px] ;
}
</style>
