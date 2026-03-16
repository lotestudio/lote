
export function useSort(state){
    const setSortBy = (name) => {
        delete state.value.urlParams.page;
        if (state.value.urlParams.s === name) {
            state.value.urlParams.o = state.value.urlParams.o === 'asc' ? 'desc' : 'asc';
        } else {
            state.value.urlParams.s = name;
            state.value.urlParams.o = 'asc';
        }
    }

    return {
        setSortBy
    }
}
