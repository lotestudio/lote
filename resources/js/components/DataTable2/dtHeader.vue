<template>
    <thead class="text-left">
    <tr>
        <template
            v-for="(column,index) in state.columns"
            :key="index"
        >
            <th class="font-light p-2 hover:bg-black-100"
                :class="column.classes"
                :style="column.style ?? ''"
                v-if="state.columnsVisibility[index] ?? true"
            >
                <div v-if="column.selectable" :class="column.tooltip ? 'has-tooltip' : ''">
                    <lote-toggle :defaultState="false" @input="$emit('select',$event)">
                        <template v-slot:active>
                            <span class="cursor-pointer text-xl i-checkCircle"/>
                        </template>
                        <template v-slot:inactive>
                            <span class="cursor-pointer text-xl i-circle"/>
                        </template>
                    </lote-toggle>
                    <span v-if="column.tooltip" class="tooltip">
                        {{ column.tooltip }}
                    </span>
                </div>
                <span v-else-if="!column.sort" :class="column.tooltip ? 'has-tooltip' : ''"
                >
                    {{ column.label }}
                    <span v-if="column.tooltip" class="tooltip">
                        {{ column.tooltip }}
                    </span>
                </span>
                <div class="cursor-pointer"
                     @click.stop.prevent="setSortBy(column.sort)"
                     v-else
                     :class="column.tooltip ? 'has-tooltip' : ''"
                >
                    <span>
                        {{ column.label }}
                        <span v-if="column.tooltip" class="tooltip">
                        {{ column.tooltip }}
                    </span>
                    </span>
                    <ChevronsUpDown size="16" v-if="state.urlParams.s!==column.sort" class="inline-block" />
                    <ChevronDown size="16" v-if="state.urlParams.s===column.sort && state.urlParams.o==='desc'" class="inline-block" />
                    <ChevronUp size="16" v-if="state.urlParams.s===column.sort && state.urlParams.o==='asc'" class="inline-block" />
                </div>
            </th>
        </template>
    </tr>
    </thead>
</template>

<script setup lang="ts">
import {ChevronsUpDown,ChevronDown, ChevronUp} from 'lucide-vue-next';
import {inject} from "vue";
import LoteToggle from "@/components/LoteToggle.vue";
import {useSort} from "../DataTable2/composables/useSort";


const state = inject('state');

const {setSortBy} = useSort(state);

</script>
