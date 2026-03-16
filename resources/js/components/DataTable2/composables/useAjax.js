import { useDebounceFn } from '@vueuse/core';
import { ref, shallowRef } from 'vue';

export function useAjax(state, resetColumnVisibility, props){
    const errorMessage = ref(null)
    const data = shallowRef(null) //can't see some performance issue, but let's try!
    const loading = ref(false)
    const ajax = useDebounceFn(() => {
        loading.value = true;
        axios.get(props.defaultUrl, {
            params: state.value.urlParams
        }).then(response => {

            if (state.value.columns.length === 0) {
                state.value.columns = response.data.columns;
            }

            if (state.value.columnsVisibility === undefined || state.value.columnsVisibility.length === 0) {
                resetColumnVisibility();
            }

            data.value = response.data;

            loading.value = false;
            errorMessage.value = null;
        }, error => {

            if (error.response) {
                // The request was made, and the server responded with a status code
                // that falls out of the range of 2xx
                //console.log(error.response.data);
                //console.log(error.response.status);
                //console.log(error.response.headers);


                errorMessage.value = error.response.data.message;
            } else if (error.request) {
                // The request was made, but no response was received
                // `error.request` is an instance of XMLHttpRequest in the browser and an instance of
                // http.ClientRequest in node.js
                //console.log(error.request);
                errorMessage.value = 'No response from server';
            } else {
                // Something happened in setting up the request that triggered an Error
                //console.log('Error', error.message);
                errorMessage.value = error.message;
            }

            loading.value = false;
        });
    }, 100)

    return {
        ajax,
        errorMessage,
        data,
        loading,
    }
}
