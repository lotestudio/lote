<script setup lang="ts">
import { useFilter } from "reka-ui"
import { computed, ref, watch } from "vue"
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxList } from "@/components/ui/combobox"
import { TagsInput, TagsInputInput, TagsInputItem, TagsInputItemDelete, TagsInputItemText } from "@/components/ui/tags-input"


const props = defineProps({
    options:{
        type:Array,
        required:true
    },
    selected:{
        type:Array,
        default:()=>[]
    }
});

const modelValue = ref<string[]>(props.selected)
const open = ref(false)
const searchTerm = ref("")

const { contains } = useFilter({ sensitivity: "base" })
const filteredOptions = computed(() => {
    const options = props.options.filter(i => !modelValue.value.includes(i.value))
    return searchTerm.value ? options.filter(option => contains(option.label, searchTerm.value)) : options
})

const getLabel= (value)=>{
    return props.options.find((option)=>option.value==value)?.label;
}

const emit = defineEmits(['change']);

watch(modelValue,()=>{
    emit('change',modelValue.value);
})

</script>

<template>
    <Combobox v-model="modelValue" v-model:open="open" :ignore-filter="true" openOnClick>
        <ComboboxAnchor as-child>
            <TagsInput v-model="modelValue" class="px-2 gap-2 w-full">
                <div class="flex gap-2 flex-wrap items-center">
                    <TagsInputItem v-for="item in modelValue" :key="item" :value="getLabel(item)">
                        <TagsInputItemText />
                        <TagsInputItemDelete @click="modelValue = modelValue.filter(i => i !== item)" />
                    </TagsInputItem>
                </div>

                <ComboboxInput v-model="searchTerm" as-child>
                    <TagsInputInput placeholder="Choose..." class="min-w-[200px] w-full px-1 py-0 border-none shadow-none focus-visible:ring-0 h-auto" @keydown.enter.prevent />
                </ComboboxInput>
            </TagsInput>

            <ComboboxList>
                <ComboboxEmpty />
                <ComboboxGroup class="w-full">
                    <ComboboxItem
                        class="w-full"
                        v-for="option in filteredOptions" :key="option.value" :value="option.label"
                        @select.prevent="(ev) => {
                              if (typeof ev.detail.value === 'string') {
                                searchTerm = ''
                                modelValue.push(option.value)
                              }

                              if (filteredOptions.length === 0) {
                                open = false
                              }
                            }"
                    >
                        {{ option.label }}
                    </ComboboxItem>
                </ComboboxGroup>
            </ComboboxList>
        </ComboboxAnchor>
    </Combobox>
</template>
