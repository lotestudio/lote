import { ref, onMounted, watch, computed } from 'vue';
import { useHttp } from '@inertiajs/vue3';

export function useToggleLogic(props, emit) {
    const state = ref();
    const loading = ref(false);

    const toggle = () => {
        if (loading.value) return;
        if (props.disabled) return;

        state.value = !state.value;
        if (props.url !== '') ajax();
        emit('input', state.value);
        emit('change', state.value);
    };

    const ajax = () => {
        loading.value = true;
        const data = { ...props.ajaxData };
        data[props.name] = state.value;
        data._method = props.method;
        const postData = useHttp(data);

        postData
            .post(props.url)
            .then((response) => {
                emit('update', response);
            })
            .catch(() => {
                state.value = !state.value; // undo state on error
            })
            .finally(() => {
                loading.value = false;
            });
    };

    onMounted(() => {
        state.value = props.defaultState;
    });

    watch(
        () => props.ajaxData,
        () => {
            if (!props.dont_watch_ajax_data) {
                state.value = props.defaultState;
            }
        },
    );

    const getActiveClasses = computed(() => {
        if (state.value) {
            return props.rotate ? 'opacity-100 rotate-0' : 'opacity-100';
        }
        return props.rotate ? 'opacity-0 rotate-45' : 'opacity-0';
    });

    const getInactiveClasses = computed(() => {
        if (!state.value) {
            return props.rotate ? 'opacity-100 rotate-0' : 'opacity-100';
        }
        return props.rotate ? 'opacity-0 -rotate-45' : 'opacity-0';
    });

    return {
        state,
        loading,
        toggle,
        getActiveClasses,
        getInactiveClasses,
    };
}
