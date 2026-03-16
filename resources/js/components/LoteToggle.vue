<template>
    <div>
        <div @click="toggle()" class="toggle">
            <div :class="getActiveClasses">
                <slot name="active" :loading="loading"></slot>
            </div>
            <div :class="getInactiveClasses">
                <slot name="inactive" :loading="loading"></slot>
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

//input and change share the same event:when toggle method executed. Update is fired when ajax call sucseeded.
const emit = defineEmits(["input", "change" ,"update"]);
const { state, loading, toggle, getActiveClasses, getInactiveClasses } = useToggleLogic(props, emit);
</script>
<style scoped>
 @reference "@@/app.css";
    .toggle {
        @apply relative inline-grid select-none place-content-center cursor-pointer;
    }
    .toggle > * {
        @apply col-start-1 row-start-1 duration-300 ease-out;
        transition-property: transform, opacity;
    }
</style>
