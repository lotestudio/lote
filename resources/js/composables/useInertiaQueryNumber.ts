import { router } from '@inertiajs/vue3';
import { onMounted, ref, watch, type Ref } from 'vue';

type Options = {
    /**
     * Query параметър, напр. "tab"
     */
    key: string;

    /**
     * Мин/макс за clamp. Пример: 0 .. relations.length - 1
     */
    min?: number;
    max?: number;

    /**
     * Ако key липсва или е невалиден: да запишем ли default-а в URL.
     * По подразбиране: false (не пипа URL-а на първоначален load)
     */
    writeDefaultToUrl?: boolean;

    /**
     * Inertia visit options
     */
    replace?: boolean;
    preserveState?: boolean;
    preserveScroll?: boolean;
};

function clampNumber(value: number, min: number, max: number) {
    if (!Number.isFinite(value)) return min;
    return Math.min(Math.max(value, min), max);
}

export function readNumberFromUrl(key: string): number | null {
    const params = new URLSearchParams(window.location.search);
    const raw = params.get(key);
    if (raw === null) return null;

    const parsed = Number.parseInt(raw, 10);
    if (Number.isNaN(parsed)) return null;

    return parsed;
}

/**
 * Свързва numeric state (Ref<number>) към query string (read on mount + write on change) през Inertia.
 *
 * Подходящо за tab индекс (0/1/2), пагинация, филтър с enum index и т.н.
 */
export function useInertiaQueryNumber(state: Ref<number>, options: Options) {
    const min = options.min ?? 0;
    const max = options.max ?? Number.MAX_SAFE_INTEGER;

    const isInitialized = ref(false);

    function normalize(value: number) {
        return clampNumber(value, min, max);
    }

    function writeToUrl(value: number) {
        const normalized = normalize(value);

        router.get(
            window.location.pathname,
            { [options.key]: normalized },
            {
                replace: options.replace ?? true,
                preserveState: options.preserveState ?? true,
                preserveScroll: options.preserveScroll ?? true,
            },
        );
    }

    onMounted(() => {
        const fromUrl = readNumberFromUrl(options.key);

        if (fromUrl !== null) {
            state.value = normalize(fromUrl);
        } else {
            state.value = normalize(state.value);

            if (options.writeDefaultToUrl) {
                writeToUrl(state.value);
            }
        }

        isInitialized.value = true;
    });

    watch(
        state,
        (value) => {
            if (!isInitialized.value) return;
            writeToUrl(value);
        },
        { flush: 'post' },
    );

    return {
        isInitialized,
        normalize,
        writeToUrl,
    };
}
