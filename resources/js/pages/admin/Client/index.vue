<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import DataTable2 from '@/components/DataTable2.vue'
import DtTd from '@/components/DataTable2/dtTd.vue'
import LoteAlertDialog from '@/components/LoteAlertDialog.vue';
import Icon from '@/components/Icon.vue';
import ResetButton from '@/components/DataTable2/Inertia/ResetButton.vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { toast } from 'vue-sonner'
import ClientController from '@/actions/App/Http/Controllers/Admin/ClientController';

const breadcrumbItems = [
  { title: 'Client List', href: '/admin/client' },
]

const dataTable = ref<InstanceType<typeof DataTable2>>();

const deleteClient = (id: number) => {
    router.delete(ClientController.destroy(id).url, {
        preserveScroll: true,
        onSuccess: () => {
            if (!dataTable.value) return;
            dataTable.value.refresh();
            toast.success('The Client has been deleted successfully.')
        },
    });
};

</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head :title="`Client List`" />
    <div class="p-4">
      <data-table2 :default-url="'/admin/client'" ref="dataTable">
          <template #filters="filterProps">
            <div class="flex gap-2 w-full">
              <Input
                  class="w-40 border-neutral-300 shadow-none dark:border-neutral-500 bg-white dark:bg-background"
                  placeholder="търси"
                  v-model="filterProps.urlParams.search"
                  @keyup.enter="filterProps.setFilter()"
              ></Input>
              <ResetButton @click.stop.prevent="filterProps.resetFilters()"></ResetButton>
            </div>
            <Link :href="ClientController.create.url()">
              <Button>Добави Client</Button>
            </Link>
          </template>
        <template v-slot:tr="trProps">
          <dt-td column="0">
            {{ trProps.row.company }}
          </dt-td>
          <dt-td column="1">
              <div class="flex justify-end gap-2">
                  <Button variant="secondary" @click="router.visit(ClientController.edit(trProps.row.id).url)">
                      <Icon name="edit" />
                  </Button>
                  <LoteAlertDialog
                      dialog-title="Delete Client"
                      dialog-description="Are you sure you want to delete this Client?"
                      confirm-label="Delete"
                      @confirm="deleteClient(trProps.row.id)"
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
