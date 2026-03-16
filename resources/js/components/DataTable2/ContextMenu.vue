<script setup lang="ts">

import {useClipboard} from "@vueuse/core";
import {
    ContextMenuContent,
    ContextMenuItem,
    ContextMenuPortal,
    ContextMenuRoot,
    ContextMenuTrigger,
} from 'reka-ui'
import {computed, inject, ref} from "vue";

const props = defineProps({
    getActions:Function
})

// import loteToaster from "@/plugins/LoteToast/components/LoteToaster.vue";

const cellSource = ref();
const linkSource = ref();
const rowSource = ref();
const activeTags = ref([]);
const rowActions = ref([]);

const {copy} = useClipboard();
//const toaster = inject('toaster');
const state = inject('state');

const copyCell=()=>{
    copy(cellSource.value)
    //toaster.success('Копирано!');
}
const copyRow=()=>{
    const visible_columns_labels = state.value.columns.filter((column, index) => state.value.columnsVisibility[index]).map((column) => {
        return column.label;
    });

    const rowArray = rowSource.value.split('\t');
    const labeledArray = rowArray.map((value, index) => {
        value = value.replace(/[\r\n]+/g, ' ').trim();

        return `${visible_columns_labels[index]}: ${value}`;
    });
    const formattedString = labeledArray.join('\n');

    copy(formattedString);

    //toaster.success('Копирано!');
}
const copyLink=()=>{
    copy(linkSource.value)
    //toaster.success('Копирано!');
}
const openLinkInNewTab=()=>{
    if (linkSource.value) {
        window.open(linkSource.value, '_blank');
    }
}


const handleContextMenu = (e) => {

    let element = e.target
    activeTags.value=[];
    while (element !== null) {
        if (element.tagName === 'TR') {
            rowActions.value = props.getActions(element.dataset.id);
            rowSource.value = element.innerText;
            activeTags.value.push('tr');
        }
        if (element.tagName === 'TD') {
            cellSource.value = element.innerText;
            activeTags.value.push('td');
        }
        if (element.tagName === 'TH') {
            cellSource.value = element.innerText;
            activeTags.value.push('th');
        }
        if (element.tagName === 'A') {
            linkSource.value = element.href;
            activeTags.value.push('a');
        }

        element = element.parentNode
    }
}

const actions = [
    {label: 'Copy cell', callback:copyCell, onTag:['td','th']},
    {label: 'Copy row', callback:copyRow, onTag:['tr']},
    {label: 'Copy link address', callback:copyLink, onTag:['a']},
    {label: 'Open link in new tab', callback:openLinkInNewTab, onTag:['a']},
]

const filteredActions = computed(() => {

    const rowTransformedActions = [];
    rowActions.value.forEach(action => {
        const transformedAction={
            label: action.label,
            callback: 'openLink',
            href:action.href,
        }
        rowTransformedActions.push(transformedAction);
    })

    const mainActions =  actions.filter((action) => {
        return action.onTag.some(tag => activeTags.value.includes(tag));
    })

    return [...rowTransformedActions,...mainActions];
})


function handleAction(action,event) {

    if(action.callback==='openLink' && action.href) {
        window.location.href = action.href;
    }

    if(typeof action.callback  === 'function') {
        action.callback(event);
    }
}

const disabled=ref(false);

</script>

<template>
    <ContextMenuRoot>
        <ContextMenuTrigger as-child @contextmenu="handleContextMenu" :disabled="disabled">
            <slot/>
        </ContextMenuTrigger>
        <ContextMenuPortal>
            <ContextMenuContent
                class="p-2 border flex flex-col gap-y-1 bg-background rounded text-sm z-40"
                :side-offset="5"
            >
                <ContextMenuItem
                    v-for="action in filteredActions"
                    :key="action.label"
                    as="a"
                    value="New Tab"
                    class="cursor-pointer outline-none block hover:bg-accent p-2 rounded"
                    @click="handleAction(action,$event)"
                >
                    {{ action.label }}
                </ContextMenuItem>
            </ContextMenuContent>
        </ContextMenuPortal>
    </ContextMenuRoot>
</template>
