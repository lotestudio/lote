<template>
    <tr class="hover:bg-light-200 hover:text-body" :class="trClass" v-if="show">
        <slot></slot>
    </tr>
</template>

<script setup lang="ts">
import { provide } from "vue"
const props = defineProps({
    trClass: {
        type: String,
        default: ''
    },
    index: {
        type: Number
    },
    show: {
        type: Boolean,
        default: true
    },
    row:{
        type: Object,
        required: true
    }
});

provide('row', props.row)
</script>
