<template>
    <div>
        <div @click="toggle()" class="toggle">
            <div
                 class="w-10 h-6 rounded-full border flex items-center p-0.5"
                :class="state ? 'bg-neutral-700 border-black dark:border-white justify-end' : 'border-neutral-500 ' "
            >
                <div
                    class="w-4 h-4 rounded-full"
                    :class="state ? 'bg-white' : 'bg-neutral-500'"
                >
                </div>
            </div>
            <input type="hidden" v-if="url==='' && name!==''" :name="name" :value="state">
        </div>
    </div>
</template>

<script setup lang="ts">
import { useToggleLogic } from '@/composables/useToggle.js';

    const props = defineProps({
        defaultState: {
            type: Boolean,
            default: true
        },
        url: {
            type: String,
            default: ''
        },
        name: {
            type: String,
            default: '',
        },
        ajaxData: {
            type: Object,
            default: () => ({})
        },
        method: {
            type: String,
            default: 'post'
        },
        dont_watch_ajax_data: {
            type: Boolean,
            default: false
        },
        rotate:{
            type: Boolean,
            default: false
        },
        disabled:{
            type: Boolean,
            default: false
        }
    })

//input and change share the same event:when toggle method executed. Update is fired when ajax call succeeded.
const emit = defineEmits(["input", "change" ,"update"]);
const { state, toggle } = useToggleLogic(props, emit);
</script>
<style scoped>
 @reference "@@/app.css";
    .toggle {
        @apply relative inline-grid select-none place-content-center cursor-pointer duration-300 ease-out;
    }
    .toggle > * {
        @apply col-start-1 row-start-1 duration-300 ease-out;
    }
</style>
