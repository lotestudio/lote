import type { Directive } from "vue";

export const vFocus: Directive<HTMLElement, boolean | undefined> = {
    mounted(el, binding) {
        // ако подадеш v-focus="false" -> да не фокусира
        if (binding.value === false) return;

        // изчакваме DOM да се стабилизира (Inertia/Vue)
        queueMicrotask(() => {
            (el as HTMLElement).focus?.();
        });
    },
};
