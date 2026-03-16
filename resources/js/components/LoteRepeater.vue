<script setup>

import {computed} from "vue";

const props = defineProps({
    line: {
        type: Object,
        required: true
    },
    data: {
        type: Array,
        default: ()=>[]
    },
    multiplier: {
        type: [String,Boolean],
        default: false
    },
    round: {
        type: Number,
        default: 2
    }
})

import {useEditableList} from "@/composables/useEditableList";

const {list, addItem, deleteItem, getList} = useEditableList(props.data);

const addLine = (line=null) => {

    //check if line is object with same structure a default line add else add empty default line
    if (line && typeof line === 'object' && Object.keys(line).sort().toString() === Object.keys(props.line).sort().toString()) {
        addItem(line);
    } else {
        addItem({...props.line});
    }
}

const getData = computed(() => {
    return getList();
});


const getSum = (key) => {
    const sum  = list.value.reduce((acc, item) => {
        const multiplier = props.multiplier ? parseFloat(item[props.multiplier]) : 1;
        return acc + parseFloat(item[key]) * multiplier;
    }, 0);

    return sum.toFixed(props.round);
};

</script>

<template>
    <slot name="bar" :addLine="addLine"></slot>
    <template v-for="line in list" :key="line.unique_key">
        <slot name="list" :line="line" :addLine="addLine" :deleteLine="deleteItem"></slot>
    </template>


    <slot name="info" :getData="getData" :getSum="getSum"/>
</template>

<style scoped>


</style>
