import { ref, computed, watch } from 'vue';

export function useControllable(props, emit, options = {}) {
    const { prop = 'modelValue', defaultProp = 'defaultValue', event = 'update:modelValue', onChange } = options;

    const isControlled = computed(() => props[prop] !== undefined);

    const internal = ref(isControlled.value ? props[prop] : props[defaultProp]);

    // sync ако controlled prop се промени отвън
    watch(
        () => props[prop],
        (v) => {
            if (isControlled.value) {
                internal.value = v;
            }
        },
    );

    const value = computed({
        get() {
            return isControlled.value ? props[prop] : internal.value;
        },
        set(v) {
            if (!isControlled.value) {
                internal.value = v;
            }

            emit(event, v);

            if (onChange) {
                onChange(v);
            }
        },
    });

    return value;
}
