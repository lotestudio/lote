import {ref} from "vue";

export function useHiddenRows(){

    const selectedHiddenRowsIndexes = ref([]);

    const toggleHiddenRow = (index) => {
        const hiddenIndex = index + 1;
        if (!selectedHiddenRowsIndexes.value.includes(hiddenIndex)) {        //checking weather array contain the id
            selectedHiddenRowsIndexes.value.push(hiddenIndex);               //adding to array because value doesn't exists
        } else {
            selectedHiddenRowsIndexes.value.splice(selectedHiddenRowsIndexes.value.indexOf(hiddenIndex), 1);  //deleting
        }
    }



    return {
        toggleHiddenRow,selectedHiddenRowsIndexes
    }
}
