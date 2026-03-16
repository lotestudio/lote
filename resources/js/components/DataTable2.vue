<script setup lang="ts">
import {computed, onMounted, ref, watch, provide} from "vue"
import { useAjax } from '@/components/DataTable2/composables/useAjax';
import {useDragScroll} from "@/components/DataTable2/composables/useDragScroll";
import ContextMenu from "@/components/DataTable2/ContextMenu.vue";
import {useActions} from "./DataTable2/composables/useActions";
import {useColumns} from "./DataTable2/composables/useColumns";
import {useDataTableStorage} from "./DataTable2/composables/useDataTableStorage";
import {useFilters} from "./DataTable2/composables/useFilters";
import {useHiddenRows} from "./DataTable2/composables/useHiddenRows";
import {useSelectableRows} from "./DataTable2/composables/useSelectableRows";
import DtHeader from "./DataTable2/dtHeader.vue"
import DtRow from "./DataTable2/dtRow.vue"
import Pager from "./DataTable2/Pager.vue"
// import '@@/admin/_loteDataTable2.css';


const props = defineProps({
    defaultUrl: {
        type: String,
        require: true
    },
    offset: {
        type: Number,
        default: 8
    },
    hasHiddenRow: {
        type: Boolean,
        default: false
    },
    defaultUrlParams: {
        type: Array,
        default: ()=>[]
    },
    selectable: {
        type: Boolean,
        default: false
    },
    filtersContainerClasses: {
        type: String,
        default: ''
    },
    dragScroll:{
        type: Boolean,
        default: false
    }
})




const {state, resetState, hardReset, setDefaultUrlParams} = useDataTableStorage(props.defaultUrl, props.defaultUrlParams);
const {filtersCount, setFilter, getFilter} = useFilters(state);
const {toggleColumnVisibility, resetColumnVisibility} = useColumns(state);
const {toggleHiddenRow, selectedHiddenRowsIndexes} = useHiddenRows();
const {ajax, errorMessage, data, loading} = useAjax(state, resetColumnVisibility, props);
const {toggle, selectAll, deselectAll, selected} = useSelectableRows(data, props.selectable)
const {setAction} = useActions(state, ajax, props.defaultUrl);
provide('state', state);

const tableWrapper=ref(null)

//computed
const rows = computed(() => {
    if (data.value === null) {
        return null;
    }

    if (!props.hasHiddenRow) {
        return data.value.data;
    }

    const res = [];
    for (let i = 0; i < data.value.data.length; i++) {   // for each element in the array arr
        res.push(data.value.data[i]);                  // add the current element from arr
        res.push({'hidden': true, 'data': data.value.data[i]});                     // add the new element to res
    }
    return res;
})

watch(() => state.value.urlParams, () => {
    ajax();
}, {deep: true})




const setTrClass = (index, trClass) => {
    rows.value[index].tr_class = trClass;
}

onMounted(() => {
    setDefaultUrlParams();
    ajax();

    if(props.dragScroll) {
        useDragScroll(tableWrapper.value,'vertical');
        //useDragScroll(document.getElementsByTagName('main')[0],'vertical');
    }

})

const getRowActions=(id:number)=>{
    return rows.value.find(row => Number(row.id) === Number(id))?.actions ?? [];
}


defineExpose({'refresh':ajax});

</script>

<template>
    <div>
        <div
            v-if="$slots.filters"
            :class="filtersContainerClasses"
            class="block md:flex md:items-center space-y-2 md:space-y-0 md:space-x-2 p-2 bg-muted rounded-xl my-4">
            <slot name="filters"
                  :columns="state.columns"
                  :urlParams="state.urlParams"
                  :setAction="setAction"
                  :setFilter="setFilter"
                  :getFilter="getFilter"
                  :resetFilters="resetState"
                  :hardReset="hardReset"
                  :columnsVisibility="state.columnsVisibility"
                  :toggleColumnVisibility="toggleColumnVisibility"
                  :resetColumnVisibility="resetColumnVisibility"
                  :total="data?.total"
                  :filtersCount="filtersCount"
            ></slot>
        </div>

        <div v-if="data?.info" v-html="data?.info"></div>

        <div v-if="selectable" class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <div v-if="selected.length>0">
                    <slot name="selected_action" :selected="selected" :urlParams="state.urlParams"/>
                </div>
            </div>
            <div>
                <div class="p-2">Избрани: {{ selected.length }}</div>
            </div>
        </div>


        <div v-if="errorMessage" class="w-full bg-red-100 dark:bg-red-900 z-10 p-4 rounded">
            {{ errorMessage }}
        </div>


        <div class="loteDataTableWrapper" ref="tableWrapper">
            <context-menu :getActions="getRowActions">
                <table class="loteDataTable2" v-if="data!==null">
                    <dt-header
                        @select="$event ? selectAll() : deselectAll()"
                    ></dt-header>
                    <tbody class="transition-opacity" :class="{loading:loading}">
                    <dt-row v-for="(row,index) in rows"
                            :tr-class="row.tr_class || '' "
                            :key="row.id ?? index"
                            :data-id="row.id ?? index"
                            :index="index"
                            :show="row.hidden ? selectedHiddenRowsIndexes.includes(index) : true"
                            @click="toggle(row.id)"
                            :row="row"
                    >
                        <slot v-if="!row.hidden" name="tr"
                              :row="row"
                              :index="index"
                              :ajax="ajax"
                              :items="data"
                              :urlParams="state.urlParams"
                              :toggleHiddenRow="()=>toggleHiddenRow(index)"
                              :setTrClass="setTrClass"
                              :isHiddenVisible="selectedHiddenRowsIndexes.includes(index+1)"
                              :isSelected="selected.findIndex((id)=>Number(id)===Number(row.id))>-1"
                        ></slot>


                        <slot v-if="row.hidden" name="tr-hidden" :row="row" :index="index"
                              :isSelected="selected.findIndex((id)=>Number(id)===Number(row.id))>-1"
                        ></slot>
                    </dt-row>
                    </tbody>
                </table>
            </context-menu>
            </div>

        <Pager v-if="data!==null && errorMessage===null" :data="data" :offset="offset"
               @change="state.urlParams.page = $event"/>
    </div>
</template>
