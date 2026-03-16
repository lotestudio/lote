import {ref} from "vue";

export function useSelectableRows(data,selectable){

    const selected = ref([])

    const selectAll = () => {
        selected.value = selected.value.concat(data.value.data.map((item) => item.id).filter((item) => selected.value.indexOf(item) < 0))
    }
    const deselectAll = () => {
        selected.value.length = 0;
    }

    const toggle = (id) => {
        if (selectable) {
            const index = selected.value.findIndex((selected_id) => Number(selected_id) === Number(id));
            if (index > -1) {
                selected.value.splice(index, 1);
            } else {
                selected.value.push(id);
            }
        }
    }

    return {
        toggle,selectAll,deselectAll,selected
    }
}
