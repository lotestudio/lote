import { router } from '@inertiajs/vue3';
import type { Ref } from 'vue';
import { toast } from 'vue-sonner';


type WayFinderController = {
    destroy: (id: number | string) => { url: string };
};

export function useDeleteAction(
    controller: WayFinderController,
    dataTable: Ref,
) {
    const deleteAction = (id: number | string) => {
        router.delete(controller.destroy(id).url, {
            preserveScroll: true,
            onSuccess: () => {
                if (!dataTable.value) return;
                dataTable.value.refresh();
                toast.success('Записът беше изтрит успешно.');
            },
        });
    };

    return {
        deleteAction,
    };
}
