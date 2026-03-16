import {computed} from "vue";

export function useFilters(state){

    const filtersCount = computed(() => {
        const res = Object.keys(state.value.urlParams).filter((key) => {
            const isEmpty = state.value.urlParams[key] === '' || state.value.urlParams[key].length === 0;
            return key.startsWith('filters') && !isEmpty;
        });
        return res.length;
    })

    const setFilter = (name, value) => {
        delete state.value.urlParams.page;

        if (name) {
            if(name === 'limit'){
                state.value.urlParams['limit'] = value;
            }else {
                if (value === '') {
                    delete state.value.urlParams['filters[' + name + ']'];
                } else {
                    state.value.urlParams['filters[' + name + ']'] = value;
                }
            }
        }
    }

    const getFilter = (name) => {
        return state.value.urlParams['filters['+name+']'];
    }

    return {
        filtersCount,
        setFilter,
        getFilter
    }
}
