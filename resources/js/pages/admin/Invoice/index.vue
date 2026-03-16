<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import DataTable2 from '@/components/DataTable2.vue';
import DtTd from '@/components/DataTable2/dtTd.vue';
import LoteAlertDialog from '@/components/LoteAlertDialog.vue';
import Icon from '@/components/Icon.vue';
import ResetButton from '@/components/DataTable2/Inertia/ResetButton.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner';
import InvoiceController from '@/actions/App/Http/Controllers/Admin/InvoiceController';

const breadcrumbItems = [{ title: 'Invoice List', href: '/admin/invoice' }];

const dataTable = ref<InstanceType<typeof DataTable2>>();

const deleteInvoice = (id: number) => {
    router.delete(InvoiceController.destroy(id).url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!dataTable.value) return;
            dataTable.value.refresh();
            toast.success('The Invoice has been deleted successfully.');
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`Invoice List`" />
        <div class="p-4">
            <data-table2 :default-url="'/admin/invoice'" ref="dataTable">
                <template #filters="filterProps">
                    <div class="flex w-full gap-2">
                        <Input
                            class="w-40 border-neutral-300 bg-white shadow-none dark:border-neutral-500 dark:bg-background"
                            placeholder="търси"
                            v-model="filterProps.urlParams.search"
                            @keyup.enter="filterProps.setFilter()"
                        ></Input>
                        <ResetButton @click.stop.prevent="filterProps.resetFilters()"></ResetButton>
                    </div>
                    <Link :href="InvoiceController.create.url()">
                        <Button>Добави Invoice</Button>
                    </Link>
                </template>
                <template v-slot:tr="trProps">
                    <dt-td column="0">
                        {{ trProps.row.num }}
                    </dt-td>
                    <dt-td column="1">
                        <Button variant="destructive" @click="router.visit('/admin/copy_invoice/' + trProps.row.id + '')"> Copy </Button>
                    </dt-td>

                    <dt-td column="2">
                        <a :href="'/admin/invoice/' + trProps.row.id + ''" class="text-sky-500">{{ trProps.row.client }}</a>
                    </dt-td>
                    <dt-td column="3">
                        {{ trProps.row.total }}
                    </dt-td>
                    <dt-td column="4">
                        {{ trProps.row.date }}
                    </dt-td>
                    <dt-td column="5">
                        <div class="flex justify-end gap-2">
                            <a :href="'/admin/download/' + trProps.row.id + ''">
                                <Button variant="default"> PDF original </Button>
                            </a>
                            <a :href="'/admin/download/' + trProps.row.id + '/copy'">
                                <Button variant="default"> PDF copy </Button>
                            </a>
                            <Button variant="secondary" @click="router.visit(InvoiceController.edit(trProps.row.id).url)">
                                <Icon name="edit" />
                            </Button>
                            <LoteAlertDialog
                                dialog-title="Delete Invoice"
                                dialog-description="Are you sure you want to delete this Invoice?"
                                confirm-label="Delete"
                                @confirm="deleteInvoice(trProps.row.id)"
                            >
                                <Button variant="destructive" size="icon">
                                    <span class="i-trash"></span>
                                </Button>
                            </LoteAlertDialog>
                        </div>
                    </dt-td>
                </template>
            </data-table2>
        </div>
    </AppLayout>
</template>
