import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

type WayFinderController = {
    create: { url: () => string };
    edit: (id: number) => { url: string };
};

export function useRelation(
    relationKey: string | undefined,
    relationValue: string | number | undefined,
    controller: WayFinderController,
) {
    const getDefaultParams = computed(() => {
        if (!relationKey || !relationKey) return [];

        return [{ [`filters[${relationKey}]`]: relationValue }];
    });

    const getCreateUrl = computed(() => {
        if (!relationKey || !relationKey) return controller.create.url();

        const u = new URL(controller.create.url(), window.location.origin);
        u.searchParams.set(relationKey, relationValue + '');
        u.searchParams.set('return_url', window.location.href);

        return u.toString();
    });

    const editAction = (id: number) => {
        if (relationKey && relationValue) {
            router.visit(
                controller.edit(id).url + '?return_url=' + window.location.href,
            );
        } else {
            router.visit(controller.edit(id).url);
        }
    };

    return {
        getDefaultParams,
        getCreateUrl,
        editAction,
    };
}
