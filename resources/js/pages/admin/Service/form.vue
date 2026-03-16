<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3'
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useInitialValue } from '@/composables/useInitialFormValue';
import AppLayout from '@/layouts/AppLayout.vue'

const page = usePage();
const { get, isEdit } = useInitialValue();

const form = useForm({
    invoice_id: get('invoice_id','')
});

function submit() {
  if (isEdit) {
    form.put('/admin/service/' + page.props.model.id)
  } else {
    form.post('/admin/service')
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="[]">
    <Head :title="isEdit ? 'Edit Service' : 'Create Service'" />
    <div class="max-w-xl p-4">
      <form @submit.prevent="submit" class="space-y-6">
        <div class="grid gap-2">
          <Label for="invoice_id">Invoice_id:</Label>
          <Input id="invoice_id" required :tabindex="1"  v-model="form.invoice_id" />
          <InputError class="mt-2" :message="form.errors.invoice_id" />
        </div>
        <Button :disabled="form.processing">Запази</Button>
      </form>
    </div>
  </AppLayout>
</template>
