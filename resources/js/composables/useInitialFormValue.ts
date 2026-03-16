import { usePage } from '@inertiajs/vue3';

/**
 * @param modelPropName - Името на prop-a, който съдържа модела (по подразбиране 'model')
 */
export function useInitialValue(modelPropName: string = 'model') {
    const page = usePage();
    const model = page.props[modelPropName] as Record<string, any> | undefined;

    // Определяме дали сме в режим на редакция
    const isEdit = !!model;

    // Взимаме query параметрите веднъж (ако сме в браузър)
    const queryParams = typeof window !== 'undefined'
        ? new URLSearchParams(window.location.search)
        : null;

    /**
     * Връща началната стойност за поле във форма.
     * Приоритет:
     * 1. Стойност от Model (Edit mode)
     * 2. Стойност от URL Query Param (Create mode with preset)
     * 3. Стойност по подразбиране (Fallback)
     *
     * @param fieldName - Името на полето (напр. 'voice_id')
     * @param fallback - Стойност по подразбиране (напр. null, '', false)
     * @param type - Тип за конвертиране ('string' | 'number' | 'boolean' | 'json')
     */
    function get<T>(fieldName: string, fallback: T, type: 'string' | 'number' | 'boolean' | 'json' = 'string'): T {
        // 1. Ако има модел и полето съществува в него -> връщаме го
        if (model && model[fieldName] !== undefined && model[fieldName] !== null) {
            return model[fieldName] as T;
        }

        // 2. Ако има query param -> връщаме го (конвертиран)
        if (queryParams && queryParams.has(fieldName)) {
            const paramVal = queryParams.get(fieldName);

            if (paramVal !== null) {
                if (type === 'number') return Number(paramVal) as unknown as T;
                if (type === 'boolean') return (paramVal === '1' || paramVal === 'true') as unknown as T;
                if (type === 'json') {
                    try {
                        return JSON.parse(paramVal) as T;
                    } catch (e) {
                        console.error(`Failed to parse JSON for param ${fieldName}`, e);
                        return fallback;
                    }
                }
                return paramVal as unknown as T;
            }
        }

        // 3. Връщаме стойността по подразбиране
        return fallback;
    }

    return { get, isEdit, model };
}
