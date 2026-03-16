<script setup lang="ts">
import {inject} from "vue";
import {useColumns} from "@/components/DataTable2/composables/useColumns";

defineProps({
    column: [Number,String],
})
const state=inject('state');
const {isColumnIndexVisible,isSortedColumn}=useColumns(state);
</script>
<template>
    <td
        v-if="isColumnIndexVisible(column)"
        :class="[{'bg-light-300': isSortedColumn(column)}, state.columns[column]?.tdClasses ?? '']"
        :style="state.columns[column]?.tdStyle ?? ''"
    >
        <slot/>
    </td>
</template>
