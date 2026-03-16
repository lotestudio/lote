export function useColumns(state){

    const toggleColumnVisibility = (index) => {
        state.value.columnsVisibility[index] = !state.value.columnsVisibility[index];
    }
    const resetColumnVisibility = () => {
        state.value.columnsVisibility = Array(state.value.columns.length).fill(true);
    }


    const isColumnVisible = (label) => {
        let index = state.value.columns.findIndex(column => column.label === label);

        if(index === -1 || state.value.columnsVisibility === undefined){
            return true;
        }

        return state.value.columnsVisibility[index] ?? true;
    }


    const isColumnIndexVisible = (index) => {
        if(index === -1 || state.value.columnsVisibility === undefined){
            return true;
        }

        return state.value.columnsVisibility[index] ?? true;
    }

    const isSortedColumn = (index) => {
        //find column by index
        let column = state.value.columns[index];


        if(!column?.sort){
            return false;
        }

        return column.sort === state.value.urlParams.s;

    }

    return {
        toggleColumnVisibility, resetColumnVisibility, isColumnVisible, isColumnIndexVisible, isSortedColumn
    }
}
